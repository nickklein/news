import pandas as pd
from sqlalchemy import create_engine, text

# Create a connection to the MySQL database
engine = create_engine('mysql+pymysql://root:root@mysql:3306/database', echo=False)

# Fetch data from the database using SQLAlchemy and then convert it to pandas DataFrame
def fetch_data(query):
    connection = engine.connect()
    result = connection.execute(text(query))
    df = pd.DataFrame(result.fetchall(), columns=result.keys())
    connection.close()
    return df

source_links = fetch_data('SELECT * FROM source_links')
tags = fetch_data('SELECT * FROM tags')
user_tags = fetch_data('SELECT * FROM user_tags')
news_tracking = fetch_data('SELECT * FROM news_tracking')
news_summary = fetch_data('SELECT * FROM news_summary')

# Calculating tag popularity
tag_popularity = news_tracking['tag_id'].value_counts().reset_index()
tag_popularity.columns = ['tag_id', 'popularity']

# Joining the data
summary_with_popularity = pd.merge(news_summary, tag_popularity, how='left', on='tag_id')
summary_with_popularity['popularity'].fillna(0, inplace=True)

# Joining with user_tags
summary_with_user_tags = pd.merge(summary_with_popularity, user_tags, how='left', on=['user_id', 'tag_id'])

# Filtering only relevant records where the user is interested in the tag
summary_with_user_tags = summary_with_user_tags[pd.notnull(summary_with_user_tags['user_tags_id'])]

# Sorting the records based on popularity
summary_with_user_tags = summary_with_user_tags.sort_values(by=['popularity', 'points'], ascending=False)

# Getting the top n articles for each user
top_articles_per_user = summary_with_user_tags.groupby('user_id').head(n=5)

print(top_articles_per_user)
