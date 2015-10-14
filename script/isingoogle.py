# -*- coding: UTF-8 -*-

import unicodedata
import openpyxl.reader.excel
import time
import datetime
import codecs
import mechanize
import cookielib
import re
import sys
from random import randint

class GoogleIsinSearch:
    def __init__(self, maxsleeptime=15, minsleeptime=3):
        self.minsleeptime   = minsleeptime
        self.maxsleeptime   = maxsleeptime
        self.nextsleep      = 0
        self.lastsearch     = datetime.datetime.now()

    def _get_browser(self):
        # Browser
        br = mechanize.Browser()

        # Cookie Jar
        cj = cookielib.LWPCookieJar()
        br.set_cookiejar(cj)

        # Browser options
        br.set_handle_equiv(True)
        #    br.set_handle_gzip(True)
        br.set_handle_redirect(True)
        br.set_handle_referer(True)
        br.set_handle_robots(False)

        # Follows refresh 0 but not hangs on refresh > 0
        br.set_handle_refresh(mechanize._http.HTTPRefreshProcessor(), max_time=1)

        # User-Agent (this is cheating, ok?)
        br.addheaders = [('User-agent', 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071615 Fedora/3.0.1-1.fc9 Firefox/3.0.1')]

        r = br.open('http://www.google.com/')
        return br

    def _overheat(self):
        timediff = (datetime.datetime.now() - self.lastsearch).seconds
        if (timediff < self.nextsleep):
            time.sleep(self.nextsleep-timediff)
        self.nextsleep      = randint(self.minsleeptime, self.maxsleeptime)
        self.lastsearch     = datetime.datetime.now()



    def search_google(self, company):
        self._overheat()
        br = self._get_browser()
        tried=0
        while True:
            try:
                # Select the first (index zero) form
                br.select_form(nr=0)
                # Let's search
                #Convert search string to unicode removing other characters
                searchstring = ''.join([i if ord(i) < 128 else ' ' for i in company]) + ' isin'
                try:
                    br.form['q'] =  searchstring
                except Exception as e:
                    print "EXPTION 1", e
                    br.select_form(nr=0)
                    br.form['q'] = searchstring

                br.submit()
            except (mechanize.HTTPError,mechanize.URLError) as e:
                tried += 1
                if isinstance(e,mechanize.HTTPError):
                    print e.code
                else:
                    print e.reason.args
                if tried > 4:
                    return
                time.sleep(30)
                br = self_get_browser()
                continue
            except Exception as e:
                print "Unexpected error:", sys.exc_info()[0], e
                tried += 1
                #print list(br.forms())
                time.sleep(30)
                if tried > 4:
                    return
            break

        occurances = {}

        for line in br.response().read().split('\n'):
            isinResult = re.search( r'[A-Z]{2}[0-9]{1}[0-9A-Z]{8}[0-9]{1}', line, flags=0)
            if isinResult:
                #print company, " : ", isinResult.group()
                if isinResult.group() in occurances:
                    occurances[isinResult.group()] += 1
                else:
                    occurances[isinResult.group()] = 1
            else:
                #print "No match!!", company
                pass

        print occurances

        # Find the most common ISIN result in the search
        if len(occurances) > 0:
            maxOccurences = max(occurances.values())
            return max(occurances.iterkeys(), key=(lambda key: occurances[key])), maxOccurences, occurances
        else:
            return "Not found", 0, {}


    def writetocsvfile(file, name, isin, count, delimiter):
        if isin is None:
            isin = ""
        if name is None:
            name = ""
        if count is None:
            count = 0
        try:
            file.write(isin + delimiter + name + delimiter + str(count) + "\n")
        except IOError as detail:
            print "Error, problems writing to file: ", detail
        except UnicodeEncodeError as detail:
            print detail


if __name__ == "__main__":
    #Parse arguments
    filename = str(sys.argv[1])

    if len(sys.argv) > 2:
        sheetName = sys.argv[2]
    else:
        sheetName = 'Sheet1'

    # Output file
    timenow = datetime.datetime.now()
    # Write mode creates a new file or overwrites the existing content of the file.
    # Write mode will _always_ destroy the existing contents of a file.
    try:
        # This will create a new file or **overwrite an existing file**.

        f = codecs.open(filename + "_isincodes_" + timenow.isoformat() + ".csv", 'wb', "cp1252")
    except IOError as detail:
        print "Error, problems writing to file: ", detail

    # Open import file

    workbook = openpyxl.load_workbook(filename = filename, use_iterators = True)
    worksheet = workbook.get_sheet_by_name(sheetName)
    data = []

    i = 0
    for row in worksheet.iter_rows():
        print row[0].internal_value
        print row[1].internal_value
        try:
            data = {
                'name':  row[0].internal_value
            }
        except AttributeError as detail:
            print "AttributeError", detail
        except TypeError:
            print "TypeError"
        if(data):
            name = data['name'].encode("utf-8").translate(None, '!@#$;,')
            print name

            isin, count, occurances = search_google(data['name'].encode("utf-8"))
            writetocsvfile(f, data['name'], isin, count, "\t")

        if (i > 9999): # How many rows to handle?
            break;
        i=i+1
        time.sleep(randint(0,15))

    f.close()
