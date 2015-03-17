#-*- coding: utf-8 -*-
import linksc
import argparse
import mysql.connector
from random import randint

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("-f", "--fund", help="Fund to use.")

    args = parser.parse_args()

    linksc = linksc.LinkSC(args.fund)

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

    #names =  [('Alstom SA', )]

    for (name,) in names: 
        linksc.db_name(name)
