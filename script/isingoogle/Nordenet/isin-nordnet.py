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


def getNordnetIsin(br, nordnetUrl):
    r = br.open(nordnetUrl)

    result = re.search( r'<iframe src="?\'?([^"\'"]*)', r.read(), flags=0)
    if result:
        msseUrl = result.group()[13:]
        print msseUrl
    else:
        print "no go\n"

    r = br.open(msseUrl)

    print
    for line in r.read().split('\n'):
        isin = None
        isinResult = re.search( r'[A-Z]{2}[0-9]{1}[0-9A-Z]{8}[0-9]{1}', line, flags=0)
        if isinResult:
            print isinResult.group()
            isin = isinResult.group()
            return isin

        else:
            #print "No match!!", company
            pass

def writetocsvfile(file, isin, name, delimiter):
    print isin + delimiter + name +"\n"
    if isin is None:
        isin = ""
    if name is None:
        name = ""
    try:
        file.write(isin + delimiter + name.decode('utf8', 'ignore') +"\n")
    except IOError as detail:
        print "Error, problems writing to file: ", detail
    except UnicodeEncodeError as detail:
        print detail


#Parse arguments
filename = str(sys.argv[1])

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

# Browser
br = mechanize.Browser()

# Cookie Jar
cj = cookielib.LWPCookieJar()
br.set_cookiejar(cj)

# Browser options
br.set_handle_equiv(True)
br.set_handle_gzip(True)
br.set_handle_redirect(True)
br.set_handle_referer(True)
br.set_handle_robots(False)

# Follows refresh 0 but not hangs on refresh > 0
br.set_handle_refresh(mechanize._http.HTTPRefreshProcessor(), max_time=1)
# User-Agent (this is cheating, ok?)
br.addheaders = [('User-agent', 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071615 Fedora/3.0.1-1.fc9 Firefox/3.0.1')]

# Open import file


workbook = openpyxl.load_workbook(filename = filename, use_iterators = True)
worksheet = workbook.get_sheet_by_name(sheetName)
data = []


i = 0
for row in worksheet.iter_rows():
    try:
        data = {
            'name':  row[0].internal_value,
            'url': row[1].internal_value

        }
    except AttributeError as detail:
        print "AttributeError", detail
    except TypeError:
        print "TypeError"
    if(data):
        name = data['name'].encode("utf-8").translate(None, '!@#$€£&;,')
        if (data['url']):
            url = 'http://www.nordnet.se' + data['url'].encode("utf-8")
            isin = getNordnetIsin(br, url)
        else:
            isin = 'Not found'


        writetocsvfile(f, name, isin, "\t")

    if (i > 9999): # How many rows to handle?
        break;
    i=i+1
    #time.sleep(randint(0,15))

f.close()
