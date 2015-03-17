#-*- coding: utf-8 -*-
import findsc
import argparse
import mysql.connector
from random import randint

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("-f", "--fund", help="Fund to use.")
    parser.add_argument("-g", "--google", help="Google list",  action='store_true')
    parser.add_argument("-d", "--dbsearch", help="dbsearch",  action='store_true')

    args = parser.parse_args()

    findsc = findsc.FindSC(args.fund)

    cnx  = mysql.connector.connect(
                                user='root', 
                                password='root', 
                                database='fund_search')
    cursor = cnx.cursor()
    query_names = (
            "SELECT name from tmp_shareholding "
            "where fund = (select id from tmp_fund where name = %s)")
 
    cursor.execute(query_names, (args.fund, ))
    names = cursor.fetchall()
    cursor.close()
    cnx.close()

    for (name,) in names: 
        print name
        if(args.google):
            findsc.google_name(name)
        if(args.dbsearch):
            findsc.db_name(name)
