#-*- coding: utf-8 -*-
import sys
import findisin
import mysql.connector

"""
Get a sc from a name and print to fund_search.tmp_shareholding
"""
class LinkSC:

    def __init__(self, source): 
        self.finder = findisin.FindIsin()
        # INIT SOURCE AS PARAMETER ?
        self.source = source
        self.cnxUp  = mysql.connector.connect(
                                user='root', 
                                password='root', 
                                database='fund_search')

    # GIVE NAME -> SEARCHES GOOGLE FOR ISIN 
    # -> UPDATES DATABASE WITH RESULTS BACKSEaRCHED IN DB

    def db_name(self, name):
        share_company = None
        share = None

        share = self.finder.find_share_routine(name)
        share_exact = self.finder.find_exact_share_routine(name)

        if (share_exact is not None): 
            share = share_exact

        if(share is None): 
            share = self.finder.find_share_alt_name(name)

        if (share is not None):
            share_company = self.finder.share_company_by_isin(share[1])

        if(share_company is None):
            share_company = "---------------"

        self._update_share_company(name, self.source, share_company)

        
    def _update_share_company(self, name, source, share_company):

        DB_QUERY = ("UPDATE tmp_shareholding "
            "SET share_company = %s "
            "WHERE name = %s AND fund = (select id from tmp_fund where name = %s)")

        cursorUp = self.cnxUp.cursor()
    
        cursorUp.execute(DB_QUERY, (share_company, name, source ))  
        cursorUp.close()
        self.cnxUp.commit()
