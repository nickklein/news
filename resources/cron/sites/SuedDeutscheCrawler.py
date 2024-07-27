import re
import time
from datetime import datetime
from dateutil.parser import parse
from sites.GeneralCrawler import GeneralCrawler
import arrow

class SuedDeutscheCrawler(GeneralCrawler):
    # Check if article elements exist on the page. Return boolean
    def init(self, htmlDump):
        elementsTitle = ['article .css-1r9juou']
        elementsParagraphs = ['article .css-1r7yllh']
        paidGate = ['.pay-furtherreading']
        if self.hasArticleElements(htmlDump, elementsTitle) and self.hasPayGate(htmlDump, paidGate) is False:
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
