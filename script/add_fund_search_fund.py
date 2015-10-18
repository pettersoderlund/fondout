#-*- coding: utf-8 -*-

"""
Import a fund with holdings from a xls(x)-file to fund_search DATABASE
"""

import sys
import mysql.connector
import openpyxl.reader.excel
import argparse

class AddFundSearchFund:

    def __init__(self):
        self.cnx  = mysql.connector.connect(
                                user='root',
                                password='root',
                                database='fund_search')

    def _add_new_fund(self, name, currency='SEK', date='', total_aum=0):
        cursor = self.cnx.cursor()
        DB_QUERY = ("INSERT INTO tmp_fund"
            "(name, currency, date, total_market_value) "
            "VALUES (%s, %s, %s, %s) "
            " ON DUPLICATE KEY UPDATE name = name")

        print DB_QUERY

        if (type(total_aum) is str):
            total_aum = total_aum.replace(" ", "")

        cursor.execute(DB_QUERY,
                (name.strip(" "), currency.strip(" "), date, total_aum))

        fund_id = cursor.lastrowid
        cursor.close()
        self.cnx.commit()
        return fund_id

    def _add_share_holding(self, name, fund_id, weight=0, market_value=0, note=''):
        DB_QUERY = ("INSERT INTO tmp_shareholding "
            "(name, fund, weight, market_value, note) "
            "VALUES (%s, %s, %s, %s, %s)")

        cursor = self.cnx.cursor()

        if (type(weight) is str):
            weight = weight.replace(" ", "")

        if (type(market_value) is str):
            market_value.replace(" ", "")

        cursor.execute(DB_QUERY,
            (name.strip(" "),
            fund_id,
            weight,
            market_value,
            note
            )
        )
        cursor.close()
        self.cnx.commit()

    def main(self):
        parser = argparse.ArgumentParser()
        parser.add_argument("-f", "--filename", help="File to import fund from")
        args = parser.parse_args()


        filename = args.filename
        # Open import file
        workbook = openpyxl.load_workbook(filename = filename, use_iterators = True)
        for worksheet in workbook:
            data = []
            i = 0

            fund_name    = worksheet['B2'].value
            fund_isin    = worksheet['B3'].value
            fund_manager = worksheet['B4'].value
            fund_aum     = worksheet['B5'].value
            fund_curr    = worksheet['B6'].value
            fund_date    = worksheet['B7'].value
            fund_info = (fund_name, fund_isin, fund_manager, fund_aum,
                fund_curr, fund_date)

            print fund_info
            print 'Add funding fund ' + fund_name
            fund_id = self._add_new_fund(fund_name, fund_curr, fund_date, fund_aum)
            print " fund id_ " + str(fund_id)

            rows = worksheet.iter_rows()

            #jump to row 10
            j = 0
            while (j < 9):
                rows.next()
                j = j + 1

            for row in rows:
                if(row):
                    if (row[2].value):
                        share_name          = row[2].value
                        share_market_value  = row[1].value
                        share_isin          = row[0].value
                        share_weight        = ''
                        share_note          = ''

                        if (len(row)>3):
                            share_weight = row[3].value
                        if (len(row)>4):
                            share_note   = row[4].value

                        print share_name
                        # self._add_share_holding()
                        self._add_share_holding(
                            share_name,
                            fund_id,
                            share_weight,
                            share_market_value,
                            share_note
                            )

                if (i > 9999): # How many rows to handle?
                    break;
                i=i+1
if __name__ == "__main__":
    afsf = AddFundSearchFund()
    afsf.main()
