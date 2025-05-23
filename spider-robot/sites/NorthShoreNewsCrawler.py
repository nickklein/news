import re
import time
from datetime import datetime
from dateutil.parser import parse
from sites.GeneralCrawler import GeneralCrawler
import arrow

class NorthShoreNewsCrawler(GeneralCrawler):
    # Check if article elements exist on the page. Return boolean
    def init(self, htmlDump):
        elementsTitle = ['.details-title']
        elementsParagraphs = ['.details-body p']
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
