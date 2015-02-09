""" 
Use YQL to retreive old market caps by symbol 
"""

""" 
Usage: python oldmkt.py input_file.tsv 2014-06-30 
inputfile format col0: symbol


"""

import sys
import csv
import stockretriever
import getopt
import time
import datetime
from random import randint

"""
To do:
	- input options could be made better.. simple filenames on incoming files atm req.
	- Program crashing on
	Traceback (most recent call last):
  		File "oldmkt.py", line 74, in <module>
    		if (row['share_price'] and row['current_share_price']):
		KeyError: 'current_share_price'
	- Retry 4 times on failed YQL/yahoo - queries, look at isingoogle script
	- Symbol name in coloumn:  ticker - name - currency - mktcap letters yahoo

Fixed:
	- Sleep between queries -- OK
	- Name outputfiles by date -- OK
	- reset variables after row -- OK
	- Progress indicators wanted. -- OK

"""

print 'Number of arguments:', len(sys.argv), 'arguments.'
if len(sys.argv) != 3:
	print "python oldmkt.py <infile.tsv> <date in format: YYYY-MM-DD>"
	sys.exit(2)

print 'Argument List:', str(sys.argv)


#date = '2014-06-30'
date = sys.argv[2]
inputfilename = sys.argv[1]
#symbols = ['YHOO', 'GOOG', 'MSFT']
#symbols = ["ARLP", "MGYOY", "U06.SI", "PGIL.L"]
symbols = ['PCHUY', 'RIO', 'ITOCY', 'LUP.TO', 'AGK.AX', 'MSBHY', 'GMBXF.PK', '0002.HK', 'RNFTF.PK', 'BNPJF', 'CRNZF', '0991.HK', 'SVJTY.PK', 'PRSAF', 'EC', '0486.HK']

#with open('alibaba.tsv','rb') as inputFile, open('new.csv', 'wb') as outputFile:

timenow = datetime.datetime.now()


with open(inputfilename[0:-4] + timenow.isoformat() + '_output_old_mkt_caps.tsv', 'wb') as outputFile, open(inputfilename,'rb') as inputfile:
	inputfile = csv.reader(inputfile, delimiter='\t')
	next(inputfile)

	fieldnames = ['symbol', 'name', 'date', 'currency', 'marketcap', 'share_price', 'ratio', 'current_market_cap', 'current_share_price']
	output = csv.DictWriter(outputFile, delimiter='\t', fieldnames=fieldnames)
	output.writeheader()

	startcounter = 0
	finishcounter = 0

	sleeptime = 5 # sleeptime in sec random. 

	#for symbol in symbols:
	for symbol in inputfile:
		startcounter = startcounter + 1
		symbol = symbol[0]
		row = {}
		row = { 'symbol':symbol, 'date':date }
		# get Current mkt cap, asking price and currency

		# Reset variables
		data = {}
		oldData = []

		try:
			data = stockretriever.get_current_info([symbol])
		except TypeError as e:
			#print "Typerror {0}: {1}".format(e.errno, e.strerror)
			print "Type error, could not fetch current info on ", symbol
			data = None

		except:
			print "Error"

		if (data['MarketCapitalization'] is None):
			print "Market cap not found on ", symbol

		else:
			row['current_market_cap'] = data['MarketCapitalization']

		try:
			row['current_share_price'] = float(data['PreviousClose'])
		except:
			print "Exception", data

		try:
			row['currency'] = data['Currency']
		except:
			print "No currency error"

		# get asking price for the date we want the market cap from
		try:
			oldData = stockretriever.get_historical_info(symbol, date)
		except TypeError as e:
			#print "Typerror {0}: {1}".format(e.errno, e.strerror)
			print "Could not fetch historical info on ", symbol
			oldData = None
		except:
			print "Error {0}: {1}".format(e.errno, e.strerror)

		if(oldData is not None):
			try: 
				row['share_price'] = float(oldData['Close'])
			except Exception as e:
				print e
		else:
			row['share_price'] = " - "

		try:
			if (data is not None):
				if ((row['share_price'] is not None) and (row['current_share_price'] is not None)):
					row['ratio'] = float("{0:.2f}".format(row['share_price']/row['current_share_price']))
					if (data['MarketCapitalization'] is not None):
						currentMktCapLetters = data['MarketCapitalization']
						row['current_market_cap'] = currentMktCapLetters
						suffixMktCap = currentMktCapLetters[-1:]
						numberMktCap = float(currentMktCapLetters[0:-1])
						row['marketcap'] = str(float("{0:.2f}".format(row['ratio']*numberMktCap))) + suffixMktCap
				row['name'] = data['Name']
		except Exception as e:
			print e

		
		output.writerow(row)

		print symbol, "done. #", startcounter
		finishcounter = finishcounter + 1

		time.sleep(randint(0,sleeptime))

finishtime = datetime.datetime.now()
print "-------------- SUMMARY -------------"
print "Sleeptime used", sleeptime, "seconds"
print "Finished ", finishcounter, "/", startcounter
print "Time elapsed", finishtime-timenow
print "Time elapsed formatted", time.strftime("%H:%M:%S", (finishtime-timenow))
