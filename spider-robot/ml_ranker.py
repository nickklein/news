#!/usr/bin/python3
import pymysql.cursors
import os
from dotenv import load_dotenv
from sentence_transformers import SentenceTransformer, util

path = '/app/.env'
load_dotenv(path)

# Minimum similarity to consider a tag as "present" in an article
SIMILARITY_THRESHOLD = 0.35

class MLRanker:

    MYSQL_HOST = os.environ.get("DB_HOST")
    MYSQL_PORT = os.environ.get("DB_PORT")
    MYSQL_USER = os.environ.get("DB_USERNAME")
    MYSQL_PASSWORD = os.environ.get("DB_PASSWORD")
    MYSQL_DATABASE = os.environ.get("DB_DATABASE")
    MYSQL_CHARSET = 'utf8'

    def __init__(self):
        # Load the sentence transformer model
        self.model = SentenceTransformer('all-MiniLM-L6-v2')

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
            # Get user's source IDs
            src_ids = self.getSourceIds(cursor, user_id)
            if not src_ids:
                continue

            # Get active articles from user's sources
            articles = self.getArticles(cursor, src_ids)
            if not articles:
                continue

            # Pre-encode all tag names
            tag_embeddings = {}
            for tag in tags:
                tag_embeddings[tag['tag_id']] = {
                    'name': tag['tag_name'],
                    'embedding': self.model.encode(tag['tag_name'], convert_to_tensor=True)
                }

            # Process each article
            for article in articles:
                title = article['source_title'] or ''
                raw = article['source_raw'] or ''
                article_text = f"{title} {raw[:1000]}"

                # Encode article
                article_embedding = self.model.encode(article_text, convert_to_tensor=True)

                # Check each tag against this article
                matching_tags = []
                total_similarity = 0

                for tag_id, tag_data in tag_embeddings.items():
                    similarity = util.cos_sim(tag_data['embedding'], article_embedding).item()

                    if similarity >= SIMILARITY_THRESHOLD:
                        matching_tags.append({
                            'tag_id': tag_id,
                            'similarity': similarity
                        })
                        total_similarity += similarity

                # Only include if at least one tag matches
                if matching_tags:
                    # Base score: number of matching tags (1-5 points per tag)
                    # Bonus: similarity strength
                    num_tags = len(matching_tags)

                    for match in matching_tags:
                        # Score = base points for match + bonus for number of tags + similarity bonus
                        # More tags matched = higher score for each
                        score = int(1 + (num_tags * 2) + (match['similarity'] * 5))
                        score = min(score, 10)  # Cap at 10

                        links.append([
                            article['source_link_id'],
                            user_id,
                            match['tag_id'],
                            score
                        ])

        self.clearRank(cursor)
        if links:
            cursor.executemany(summary_sql, links)

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
