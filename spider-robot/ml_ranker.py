#!/usr/bin/python3
import pymysql.cursors
import os
from dotenv import load_dotenv
from sentence_transformers import SentenceTransformer, util

path = '/app/.env'
load_dotenv(path)

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
            SELECT users.id, tags.tag_id, tags.tag_name FROM users
            LEFT JOIN user_tags ON user_tags.user_id=users.id
            INNER JOIN tags ON user_tags.tag_id=tags.tag_id
            ORDER BY users.id ASC
        """
        cursor.execute(sql)
        userTags = cursor.fetchall()

        # Group tags by user
        user_tags_map = {}
        for tag in userTags:
            user_id = tag['id']
            if user_id not in user_tags_map:
                user_tags_map[user_id] = []
            user_tags_map[user_id].append(tag)

        for user_id, tags in user_tags_map.items():
            # Get user's source IDs
            src_ids = self.getSourceIds(cursor, user_id)
            if not src_ids:
                continue

            # Get active articles from user's sources
            articles = self.getArticles(cursor, src_ids)
            if not articles:
                continue

            # Combine all user tags into one interest profile
            tag_names = [t['tag_name'] for t in tags]
            interest_text = ' '.join(tag_names)

            # Rank articles using ML
            ranked_articles = self.rankArticles(interest_text, articles)

            # Prepare links for each tag (distribute scores across tags)
            for article in ranked_articles:
                for tag in tags:
                    links.append([
                        article['source_link_id'],
                        user_id,
                        tag['tag_id'],
                        article['score']
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

    def rankArticles(self, interest_text, articles):
        # Encode user interests
        interest_embedding = self.model.encode(interest_text, convert_to_tensor=True)

        ranked = []
        for article in articles:
            # Combine title (weighted more) and content
            title = article['source_title'] or ''
            raw = article['source_raw'] or ''

            # Title is more important, so we weight it
            article_text = f"{title} {title} {raw[:500]}"  # Repeat title, limit raw content

            # Encode and compute similarity
            article_embedding = self.model.encode(article_text, convert_to_tensor=True)
            similarity = util.cos_sim(interest_embedding, article_embedding).item()

            # Convert similarity (-1 to 1) to score (0 to 10)
            score = max(0, int((similarity + 1) * 5))

            if score > 0:
                ranked.append({
                    'source_link_id': article['source_link_id'],
                    'score': score
                })

        return ranked

    def clearRank(self, cursor):
        cursor.execute('TRUNCATE news_summary')


if __name__ == '__main__':
    MLRanker()
