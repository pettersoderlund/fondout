""" Scrape yahoo industry database through YQL """

import mysql.connector
import stockretriever
import sys

cnx = mysql.connector.connect(user='root', password='root', database='yahoo')
cursor = cnx.cursor()

add_employee = ("INSERT INTO stocks "
                "(symbol, name, industry) "
                "VALUES (%s, %s, %s) "
                "ON DUPLICATE KEY UPDATE industry=VALUES(industry)")


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
                data_employee = (company['symbol'], company['name'], industry_id) 

                try:
                    cursor.execute(add_employee, data_employee)
                except mysql.connector.errors.IntegrityError, e: 
                    print(e)
                    continue
                try:
                    print "Success adding", company['symbol'], company['name']
                except UnicodeEncodeError as e:
                    print e

                cnx.commit()

            except OSError as err:
                print(err)
            except TypeError as err:
                print(err)
            except Exception as e:
                print "Unknown error, error caught.", e
                continue

cursor.close()
cnx.close()
