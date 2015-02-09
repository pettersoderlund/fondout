""" Use YQL to set industries to share companies through fondout CLI """

""" This is a very slow script. new ways found. do not use."""

import stockretriever
from subprocess import call
import sys

sectors = stockretriever.get_industry_ids()

for sector in sectors:
    for industry in sector['industry']:
        try: 
            print "\nProcessing", industry['name'], industry['id']
        except TypeError as E:
            print E 
            continue

        industry_index = stockretriever.get_industry_index(industry['id'])

        try:
            industry_name       = industry_index['name']
            industry_companies  = industry_index['company']
            industry_id         = industry_index['id']
        except Exception, e:
            print e
            continue

        for company in industry_companies:
            try:
                if(call(["php", "/Users/petter/projects/fondout2/public/index.php", "add",  "industry-by-symbol", "--symbol=", company['symbol'], "--industry=", industry_name])): 
                    try: 
                        print "\nSuccess adding", company['name'], "(", company['symbol'], ") to", industry_name

                    except UnicodeEncodeError as e:
                        print e

                else: 
                    sys.stdout.write('.')
                    sys.stdout.flush()

            except OSError as err:
                print(err)
            except TypeError as err:
                print(err)
            except:
                print "Unknown error, error cought."
                continue