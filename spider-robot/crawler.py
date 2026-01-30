#!/usr/bin/python3
import pymysql.cursors
from bs4 import BeautifulSoup
from newspaper import Article, Config
import requests
import os
import time
import random
import arrow
from datetime import datetime
import re
from dateutil.parser import parse
from dotenv import load_dotenv


#load env file

if (True):
	path = '/var/www/vhosts/nickklein.ca/subdomains/life.nickklein.ca/.env'
else:
	path = '/home/ada/Sites/lifeautomation/core/.env'

path = '/app/.env'

load_dotenv(path)

class Crawler:

	MYSQL_HOST = os.environ.get("DB_HOST")
	MYSQL_PORT = os.environ.get("DB_PORT")
	MYSQL_USER = os.environ.get("DB_USERNAME")
	MYSQL_PASSWORD = os.environ.get("DB_PASSWORD")
	MYSQL_DATABASE = os.environ.get("DB_DATABASE")
	MYSQL_CHARSET = 'utf8mb4'

	def __init__(self):

		# Connect to the database
		connection = pymysql.connect(host=self.MYSQL_HOST,
									 port=int(self.MYSQL_PORT),
		                             user=self.MYSQL_USER,
		                             password=self.MYSQL_PASSWORD,
		                             db=self.MYSQL_DATABASE,
		                             charset=self.MYSQL_CHARSET,
		                             cursorclass=pymysql.cursors.DictCursor)

		try:
		    with connection.cursor() as cursor:
		    	self.collectLinks(cursor)
		    	connection.commit()

		    	self.crawlThroughLinks(cursor)
		    	connection.commit()
		finally:
			connection.close()

	def collectLinks(self, cursor):
		# Fetch Sources from DB that are linked to users
		sql = 'SELECT DISTINCT sources.source_id, sources.source_domain, sources.source_main_url FROM sources INNER JOIN user_sources ON user_sources.source_id=sources.source_id';
		cursor.execute(sql)
		items = cursor.fetchall()
		for item in items:
			insert_sql = ''
			sourceTitle = ''
			links = []

			# fetch and parse using BeautifulSoup
			content = self.fetchAndParseWebsite(item['source_main_url'])
			for anchor in content.find_all('a'):
				link_label = anchor.text.encode('utf-8').decode('ascii', 'ignore').strip()
				link_url = anchor.get('href', '/')

				# Remove links where the label is less than 30 characters (Articles usally have longer labels), exclude things like <img, <source, #comments, /users/
				if len(link_label) > 30 and '<img' not in link_label and '<source' not in link_label  and "#comments" not in link_url and "/users/" not in link_url and "javascript:void(0)" not in link_url and link_url != item['source_main_url']:
					#Prepare list item
					links.append([item['source_id'], self.convertToAbsoluteURL(item['source_domain'],anchor.get('href', '/')), sourceTitle])
					insert_sql =  "INSERT IGNORE INTO source_links (source_id, source_link, source_title, created_at, updated_at, active) VALUES (%s, %s, %s, now(), now(), 0)"

			cursor.executemany(insert_sql, links)


	def crawlThroughLinks(self, cursor):
		print("crawlThrougHLinks")
		#crawl through links and collect website articles

		sql = "SELECT source_links.source_link_id, source_links.source_link, sources.source_id, sources.language FROM source_links INNER JOIN sources ON source_links.source_id=sources.source_id INNER JOIN user_sources ON user_sources.source_id=sources.source_id WHERE source_links.active = 0 AND (source_links.source_id = 14 AND source_links.source_link LIKE '%www.sueddeutsche.de%' OR source_links.source_id != 14) ORDER BY RAND() LIMIT 100";

		cursor.execute(sql)
		items = cursor.fetchall()
		config = Config()
		config.browser_user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

		for item in items:
			print('Crawled: %s' % (item['source_link']))

			article = Article(item['source_link'], language=item['language'], config=config)

			try:
				article.download()
				article.parse()
				if article.text:
					update = 'UPDATE source_links SET source_title = %s, source_date = %s, source_raw = %s, active = %s, updated_at=now() WHERE source_link_id = %s'
					cursor.execute(update, (article.title, article.publish_date, article.text, 1, item['source_link_id']))
				else:
					# Can't find the article on the page. Deactivate the link so it's not used anymore
					update = 'UPDATE source_links SET active = -1, updated_at=now() WHERE source_link_id = %s'
					cursor.execute(update, (item['source_link_id']))
			except Exception as e:
				print(f"Failed to download. Reason: {e}")
				# Can't find the article on the page. Deactivate the link so it's not used anymore
				update = 'UPDATE source_links SET active = -1, updated_at=now() WHERE source_link_id = %s'
				cursor.execute(update, (item['source_link_id']))

			time.sleep(random.uniform(1, 5))  # Throttle by sleeping 1-5 seconds between requests

	def fetchAndParseWebsite(self, link_url):
		try:
			headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'}
			story = requests.get(url=link_url, headers=headers)
			return BeautifulSoup(story.content, 'html.parser')
		except KeyError:
			pass


	def convertToAbsoluteURL(self, source_domain, link):
		# Some websites use relative URLs not absolute URLS
		if source_domain in link and 'https' not in link and 'http' not in link:
			return 'http:' + link

		if source_domain not in link and 'https' not in link and 'http' not in link:
			return 'http://' + source_domain + link

		return link


crawler = Crawler()
