#-*- coding: utf-8 -*-
import sys
import findisin
from isingoogle import GoogleIsinSearch
import mysql.connector

"""
Get share_company mapping candidates and update fund_search.sc_candidate
database by search from given name with google and or db-search. 
"""
class FindSC:

    def __init__(self, source): 
        self.finder = findisin.FindIsin()
        # INIT SOURCE AS PARAMETER ?
        self.source = source
        self.cnxUp  = mysql.connector.connect(
                                user='root', 
                                password='root', 
                                database='fund_search')
        self.isingoogle = GoogleIsinSearch(maxsleeptime=10, minsleeptime=3)

    # GIVE NAME -> SEARCHES GOOGLE FOR ISIN 
    # -> UPDATES DATABASE WITH RESULTS BACKSEaRCHED IN DB
    def google_name(self, name): 
        (top_googled_isin, top_googled_isin_matches, 
               google_occurances) = self.isingoogle.search_google(name)
        # Format of recieved results
        #google_occurances = {'US6084642023': 3, 'HU0000102132': 1, 'XS0503453275': 1, 'HU0000068952': 98}

        print "google_resuults: ", google_occurances
        for isin, searched_occurances in google_occurances.iteritems(): 
            db_sharename    = None
            db_sc           = None

            # BACK SEARCH SHARE DB
            db_sharename = self.finder.share_by_isin(isin)
            # OM SHARE: SC?
            if(db_sharename is not None): 
                db_sharename = db_sharename[0]
                db_sc = self.finder.share_company_by_isin(isin)

            ## INSERT UPDATE ON DUPLICATE VALUS
            #print self.finder.share_by_isin(isin), isin, searched_occurances

            sc_candidate = (name, self.source, isin, db_sharename, db_sc, searched_occurances)
            
            for value in sc_candidate: 
                if(value is None): 
                    value = ""

            GOOGLE_QUERY = ("INSERT INTO sc_candidate(tmp_shareholding, isin, "
                    "db_sharename, db_sc, google_matches) VALUES "
                    "((select id from tmp_shareholding where name = %s and" 
                        " fund = (select id from tmp_fund where name = %s) LIMIT 1), "
                    " %s, %s, %s, %s) ON DUPLICATE KEY UPDATE "
                    "google_matches = VALUES(google_matches) ")

            cursorUp = self.cnxUp.cursor()
            print sc_candidate
            cursorUp.execute(GOOGLE_QUERY, sc_candidate)
            
            cursorUp.close()
            self.cnxUp.commit()

    # GIVE NAME
    # UPDATE DATABASE WITH RESULTS FOR EXAcT AND FUZZY SEARCHES IN SC, SHARE, ALIAS
    def db_name(self, name):
        exact_shares = self.finder.all_exact(name)
        
        # Update shares, exact match -> exact = 1
        exact = 1
        for share in exact_shares: 
            self._update_db_name(name, self.source, share[0], share[1], exact)
        
        fuzzy_shares = self.finder.all_fuzzy(name)
        fuzzy_tmp = []

        # do not add results already found in exact search
        for fuzzy in fuzzy_shares:
            if (fuzzy not in exact_shares):
                fuzzy_tmp.append(fuzzy)
        fuzzy_shares = fuzzy_tmp
        # Update shares, fuzzy match -> exact = 0
        exact = 0
        for share in fuzzy_shares: 
            self._update_db_name(name, self.source, share[0], share[1], exact)
   
    def _update_db_name(self, name, source, dbsharename, isin, exact):

        # GET SC FROM ISIN
        dbsc = self.finder.share_company_by_isin(isin)

        DB_QUERY = ("INSERT INTO sc_candidate(tmp_shareholding, isin, "
            "db_sharename, db_sc, db_exact) VALUES "
            "((select id from tmp_shareholding where name = %s and "
                " fund = (select id from tmp_fund where name = %s) LIMIT 1), "
            " %s, %s, %s, %s) ON DUPLICATE KEY UPDATE "
            "db_sharename = VALUES(db_sharename), db_sc = VALUES(db_sc), "
            "db_exact = VALUES(db_exact)")

        sc_candidate = (name, self.source, isin, dbsharename, dbsc, exact)

        cursorUp = self.cnxUp.cursor()
        print sc_candidate
        cursorUp.execute(DB_QUERY, sc_candidate)  
        cursorUp.close()
        self.cnxUp.commit()
