#!/usr/bin/python3
import pymysql.cursors
import os
import json
import requests
from dotenv import load_dotenv

path = '/app/.env'
load_dotenv(path)

OLLAMA_ENDPOINT = os.environ.get("OLLAMA_ENDPOINT", "http://100.117.210.97:11434")
OLLAMA_MODEL = os.environ.get("OLLAMA_MODEL", "qwen3:latest")
BATCH_SIZE = 15  # Articles per Ollama request


class MLRanker:

    MYSQL_HOST = os.environ.get("DB_HOST")
    MYSQL_PORT = os.environ.get("DB_PORT")
    MYSQL_USER = os.environ.get("DB_USERNAME")
    MYSQL_PASSWORD = os.environ.get("DB_PASSWORD")
    MYSQL_DATABASE = os.environ.get("DB_DATABASE")
    MYSQL_CHARSET = 'utf8'

    def __init__(self):
        connection = pymysql.connect(
            host=self.MYSQL_HOST,
            port=int(self.MYSQL_PORT),
            user=self.MYSQL_USER,
            password=self.MYSQL_PASSWORD,
            db=self.MYSQL_DATABASE,
            charset=self.MYSQL_CHARSET,
            cursorclass=pymysql.cursors.DictCursor
        )

        try:
            with connection.cursor() as cursor:
                self.processUsers(cursor)
                connection.commit()
        finally:
            connection.close()

    def processUsers(self, cursor):
        links = []
        summary_sql = 'INSERT IGNORE INTO news_summary (source_link_id, user_id, tag_id, points) VALUES (%s, %s, %s, %s)'

        # Get all users with their tags
        sql = """
            SELECT users.id as user_id, tags.tag_id, tags.tag_name FROM users
            LEFT JOIN user_tags ON user_tags.user_id=users.id
            INNER JOIN tags ON user_tags.tag_id=tags.tag_id
            ORDER BY users.id ASC
        """
        cursor.execute(sql)
        userTags = cursor.fetchall()

        # Group tags by user
        user_tags_map = {}
        for row in userTags:
            user_id = row['user_id']
            if user_id not in user_tags_map:
                user_tags_map[user_id] = []
            user_tags_map[user_id].append({
                'tag_id': row['tag_id'],
                'tag_name': row['tag_name']
            })

        for user_id, tags in user_tags_map.items():
            tag_names = [t['tag_name'] for t in tags]
            tag_ids = {t['tag_name'].lower(): t['tag_id'] for t in tags}

            # Get user's source IDs
            src_ids = self.getSourceIds(cursor, user_id)
            if not src_ids:
                continue

            # Get active articles from user's sources
            articles = self.getArticles(cursor, src_ids)
            if not articles:
                continue

            print(f"Processing {len(articles)} articles for user {user_id} with tags: {tag_names}")

            # Process in batches
            for i in range(0, len(articles), BATCH_SIZE):
                batch = articles[i:i + BATCH_SIZE]
                rankings = self.rank_with_ollama(batch, tag_names)

                for article_id, result in rankings.items():
                    score = result.get('score', 0)
                    matched_tags = result.get('tags', [])

                    if score > 0 and matched_tags:
                        for tag_name in matched_tags:
                            tag_id = tag_ids.get(tag_name.lower())
                            if tag_id:
                                links.append([
                                    int(article_id),
                                    user_id,
                                    tag_id,
                                    min(score, 10)
                                ])

        self.clearRank(cursor)
        if links:
            cursor.executemany(summary_sql, links)
            print(f"Inserted {len(links)} ranked articles")

    def rank_with_ollama(self, articles, tags):
        """Send batch of articles to Ollama for ranking"""

        # Build article list for prompt
        article_list = []
        for art in articles:
            title = art['source_title'] or 'Untitled'
            # Truncate raw content to first 500 chars for context
            raw = (art['source_raw'] or '')[:500]
            article_list.append({
                'id': art['source_link_id'],
                'title': title,
                'snippet': raw
            })

        prompt = f"""You are a news relevance ranker. Given a user's interests (tags) and a list of articles, score each article's relevance.

User's interests/tags: {', '.join(tags)}

Articles to rank:
{json.dumps(article_list, indent=2)}

For each article, respond with a JSON object mapping article ID to its ranking:
{{
  "article_id": {{"score": 1-10, "tags": ["matching", "tags"]}},
  ...
}}

Rules:
- Score 1-10 where 10 is highly relevant to the user's interests
- Only include articles with score >= 3
- "tags" should list which of the user's tags this article relates to
- Consider semantic relevance, not just keyword matching
- Respond ONLY with valid JSON, no explanation

JSON response:"""

        try:
            response = requests.post(
                f"{OLLAMA_ENDPOINT}/api/generate",
                json={
                    "model": OLLAMA_MODEL,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": 0.1,
                        "num_predict": 2000
                    }
                },
                timeout=120
            )
            response.raise_for_status()

            result = response.json()
            text = result.get('response', '')

            # Extract JSON from response
            return self.parse_ranking_response(text)

        except requests.exceptions.RequestException as e:
            print(f"Ollama request failed: {e}")
            return {}
        except Exception as e:
            print(f"Error ranking batch: {e}")
            return {}

    def parse_ranking_response(self, text):
        """Parse Ollama's JSON response into rankings dict"""
        try:
            # Try to find JSON in the response
            text = text.strip()

            # Handle thinking tags from qwen3
            if '</think>' in text:
                text = text.split('</think>')[-1].strip()

            # Find JSON object
            start = text.find('{')
            end = text.rfind('}') + 1

            if start >= 0 and end > start:
                json_str = text[start:end]
                rankings = json.loads(json_str)

                # Normalize keys to strings
                return {str(k): v for k, v in rankings.items()}

            return {}
        except json.JSONDecodeError as e:
            print(f"Failed to parse Ollama response: {e}")
            print(f"Response was: {text[:500]}")
            return {}

    def getSourceIds(self, cursor, user_id):
        cursor.execute('SELECT source_id FROM user_sources WHERE user_id = %s', (user_id,))
        return [row['source_id'] for row in cursor.fetchall()]

    def getArticles(self, cursor, src_ids):
        if not src_ids:
            return []

        placeholders = ','.join(['%s'] * len(src_ids))
        sql = f"""
            SELECT source_link_id, source_title, source_raw
            FROM source_links
            WHERE active = 1 AND source_id IN ({placeholders})
        """
        cursor.execute(sql, src_ids)
        return cursor.fetchall()

    def clearRank(self, cursor):
        cursor.execute('TRUNCATE news_summary')


if __name__ == '__main__':
    MLRanker()
