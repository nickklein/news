import re
import time
from datetime import datetime
from dateutil.parser import parse
import arrow

class GeneralCrawler:
    # Check if article elements exist on the page. Return boolean
    def init(self, htmlDump):
        elementsTitle = ['article h2', 'article h1']
        elementsParagraphs = ['article p']
        if self.hasArticleElements(htmlDump, elementsTitle):
            title = self.getTitle(htmlDump, elementsTitle)
            raw = self.getParagraphs(htmlDump, elementsParagraphs)
            datetime = self.getDateTime(htmlDump)
            return {
                "success": True,
                "title": title,
                "date": datetime,
                "raw": raw,
                "active": 1
            }

        return {
            "success": False
        }

    def hasArticleElements(self, htmlDump, elements):
        for element in elements:
            if htmlDump.select(element):
                return True
        
        return False

    def hasPayGate(self, htmlDump, elements):
        for element in elements:
            if htmlDump.select(element):
                return True

        return False
    
    # Get Title of the content    
    def getTitle(self, htmlDump, elements):
        title = ''
        for element in elements:
            if htmlDump.select(element):
                for titleElement in htmlDump.select(element):
                    title = titleElement.text.encode('utf-8').decode('ascii', 'ignore').strip()
        
        return title
    
    # Get Article content
    def getParagraphs(self, htmlDump, elements):
        paragraphsDump = ''
        for element in elements:
            for paragraphElement in htmlDump.select(element):
                paragraphsDump += paragraphElement.text.encode('utf-8').decode('ascii', 'ignore')

        return paragraphsDump

    def getDateTime(self, htmlDump):
        success = 0
        timestamp = arrow.now()
        time = timestamp.format('YYYY-MM-DD HH:mm:ss')
		#Fetching time is a bit tricky. Different websites use different elements, classes and date formats to display their time
        if htmlDump.findAll("div", {"class" : re.compile('date.*')}):
            success = 1
            dates = htmlDump.findAll("div", {"class" : re.compile('date.*')})
            try:
                timestamp = dates[0]['data-seconds']
            except KeyError:
                pass

        if htmlDump.find("p", {"class" : re.compile('date.*')}):
            success = 1
            dates = htmlDump.find("p", {"class" : re.compile('date.*')})
            try:
                timestamp = dates.text
            except KeyError:
                pass


        if htmlDump.find("time"):
            success = 1
            dates = htmlDump.find("time")
            try:
                timestamp = dates['datetime']
            except KeyError:
                pass
            try:
                timestamp = dates['data-timestamp']
            except KeyError:
                pass

        # Only format time if something is found
        if success and len(timestamp) > 8:
            if timestamp.isdigit() is False:
                timestamp = parse(timestamp,fuzzy=True)

                if isinstance(timestamp,str):
                    # Some date formats have a weird string format with 000, that needs to be divided by 1000
                    if timestamp[-3:] == '000':
                        timestamp = arrow.get(int(timestamp) / 1000)
                    else:
                        # If there's a + in the string, remove the rest
                        if "+" in timestamp:
                            print('+')
                            timestamp = timestamp.split("+")
                            timestamp = timestamp[0]
                timestamp = arrow.get(timestamp)
                time = timestamp.format('YYYY-MM-DD HH:mm:ss')

            return time
        
        return time