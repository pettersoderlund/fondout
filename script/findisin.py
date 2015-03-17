#-*- coding: utf-8 -*-

""" Use name to identify share isin by using fondout db and google """

"""
    1. Lista pa okända shares vi vill söka isin på.
    2. Traversera lista.
    3. Kontrollera mot shares
    4. Kontrollera mot share_company
    5. Kontrollera mot shares LIKE från båda håll min chars 5
    6. Kontrollera mot shares_company LIKE från båda håll min chars 5
    7. Googla namn + isin. 

    Jämför google resultat med det vi ev. hittat i databasen. 
    Om vi väljer google res. på konflikt söker vi baklänges och ser om det
    resultatet finns i databasen. 

    Olika resultatskoder skrivs ut i databasen. 

    TODO: 
        - Bryta ut parametrar för gränsvärden i variabler
        - Bryt ut lösenord och användarnamn till ENV_VAR
"""

import sys
import mysql.connector
import isingoogle
import argparse
import time
from random import randint
import signal


class FindIsin:
    def exit_procedure(): 
        print "Exiting script..."
        self.cnxUp.commit()
        self.cursorUp.close()
        self.cnxUp.close()
        self.cursor.close()
        self.connection.close()
        sys.exit(0)

    def signal_handler(signal, frame):
            print('You pressed Ctrl+C!')
            exit_procedure()

    def _execute_share_search_query(self, query, name): 
        cursor = self.connection.cursor(buffered=True)
        try:
            cursor.execute(query, (name, ))
        except mysql.connector.errors.IntegrityError, e: 
            print ('Find share by name exception:', e)
        if (cursor is not None):
            self.share = cursor.fetchone()
        else:
            self.share = None

        cursor.close()
        return self.share


    def _execute_share_search_query_get_all(self, query, name): 
        cursor = self.connection.cursor(buffered=True)
        try:
            cursor.execute(query, (name, ))
        except mysql.connector.errors.IntegrityError, e: 
            print ('Find all shares exception:', e)
        if (cursor is not None):
            self.share = cursor.fetchall()
        else:
            self.share = None

        cursor.close()
        return self.share



    def _find_by_share_exact_name(self, name):
        query = ("SELECT s.name, s.isin FROM share s "
            "WHERE s.name = (%s) "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_exact_alias(self, name):
        query = ("SELECT sa.name, s.isin "
            "FROM share s "
            "JOIN share_alias sa on sa.share = s.id "
            "WHERE sa.name = (%s) "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER + 
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_company_exact_name(self, name):
        query = ("SELECT sc.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE sc.name = (%s) "
            + self.QUERY_WHERE_AND + 
            "GROUP BY sc.name "
            + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_company_fuzzy_name(self, name):
        query = ("SELECT sc.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE sc.name like CONCAT('%', %s, '%') "
            + self.QUERY_WHERE_AND + 
            "GROUP BY sc.name "
            + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_fuzzy_name(self, name):
        query = ("SELECT s.name, s.isin " 
            "FROM share s "
            "WHERE s.name like CONCAT('%', %s, '%') "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_reverse_fuzzy_name(self, name):
        query = ("SELECT s.name, s.isin " 
            "FROM share s "
            "WHERE %s like CONCAT('%', s.name, '%') "
            "AND length(s.name) > 4 "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_fuzzy_alias(self, name):
        query = ("SELECT sa.name, s.isin "
            "FROM share s "
            "JOIN share_alias sa on sa.share = s.id "
            "WHERE sa.name like CONCAT('%', %s, '%') "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def _find_by_share_reverse_fuzzy_alias(self, name):
        query = ("SELECT sa.name, s.isin "
            "FROM share s "
            "JOIN share_alias sa on sa.share = s.id "
            "WHERE %s like CONCAT('%', sa.name, '%') "
            "AND length(sa.name) > 4 "
            + self.QUERY_WHERE_AND + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)


    def _find_by_share_company_reverse_fuzzy_name(self, name):
        query = ("SELECT s.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE %s like CONCAT('%', sc.name, '%') "
            "AND length(s.name) > 4 "
            + self.QUERY_WHERE_AND + 
            "group by sc.name "
            + self.QUERY_ORDER +
            "LIMIT 1")
        return self._execute_share_search_query(query, name)

    def share_by_isin(self, isin):
        cursor = self.connection.cursor(buffered=True)
        query = ("SELECT name FROM share s "
            "WHERE isin = (%s) "
            "AND (s.category = 1 or s.category is null) "
            "LIMIT 1")
        try:
            cursor.execute(query, (isin, ))
        except mysql.connector.errors.IntegrityError, e: 
            print ('Find share by isin exception:', e)
        if (cursor is not None):
            share_name = cursor.fetchone()
        else:
            share_name = None

        cursor.close()
        return share_name

    def share_company_by_isin(self, isin):
        cursor = self.connection.cursor(buffered=True)
        query = ("SELECT sc.name FROM share s "
            "join share_company sc on s.share_company = sc.id "
            "WHERE isin = %s ")
        try:
            cursor.execute(query, (isin, ))
        except mysql.connector.errors.IntegrityError, e: 
            print ('Find share_company by isin exception:', e)
        if (cursor is not None):
            self.share_company_name = cursor.fetchone()
        else:
            self.share_company_name = None

        cursor.close()

        if (self.share_company_name is not None):
            self.share_company_name = self.share_company_name[0]

        return self.share_company_name

    def find_exact_share_routine(self, name): 
        self.share = None

        self.share = self._find_by_share_exact_name(name)
        if (self.share is None):
            self.share = self._find_by_share_exact_alias(name)
        if (self.share is None):
            self.share = self._find_by_share_company_exact_name(name)

        return self.share

    def find_share_routine(self, name):
        self.share = None

        self.share = self.find_exact_share_routine(name)

        # Do not allow fuzzy search on search strings shorter than four letters!
        if (len(name) > 3):
            if (self.share is None): 
                self.share = self._find_by_share_fuzzy_name(name)
            if (self.share is None): 
                self.share = self._find_by_share_company_fuzzy_name(name)
            if (self.share is None): 
                self.share = self._find_by_share_fuzzy_alias(name)
            if (self.share is None): 
                self.share = self._find_by_share_reverse_fuzzy_alias(name)
            if (self.share is None): 
                self.share = self._find_by_share_reverse_fuzzy_name(name)
            if (self.share is None): 
                self.share = self._find_by_share_company_reverse_fuzzy_name(
                    name)
        return self.share

    def exact_and_fuzzy_routine(self, name):
        share = None
        share = self.find_exact_share_routine(name)
        if(share is None): 
            share = self.find_share_routine(name)
        return share

    def all_exact(self, name): 
        shares = []

        for query in self._exact_queries:
            shares = shares + self._execute_share_search_query_get_all(query, name)

        return shares

    def all_fuzzy(self, name): 
        shares = []

        for query in self._fuzzy_queries:
            shares = shares + self._execute_share_search_query_get_all(query, name)

        return shares    

    def find_share_alt_name(self, name):
        name = name.lower()
        share = None
        used_names = []
        used_names.append(name)
        for suffix in self.company_suffix:
            new_name = name.replace(suffix, "")
            if (new_name not in used_names):
                share = self.exact_and_fuzzy_routine(new_name)
                used_names.append(new_name)
            if (share is None):
                new_name = new_name.replace(",", "").replace(".", "")
                if (new_name not in used_names):
                    share = self.exact_and_fuzzy_routine(new_name)
                    used_names.append(new_name)
            if (share is None):
                new_name = new_name.replace(" ", "")
                if (new_name not in used_names):
                    share = self.exact_and_fuzzy_routine(new_name)
                    used_names.append(new_name)
            if (share is not None):
                break
        return share

    # --------------------------------------------------------------------------
    # --------------------------------------------------------------------------

    def __init__(self):
        self.QUERY_WHERE_AND = ( "AND s.isin IS NOT NULL "
                    "AND (s.category = 1 or s.category is null) ")
        self.QUERY_ORDER     = "ORDER BY s.category desc, s.id asc "

        self.company_suffix =[' group', '.', ',', ' corporation', ' group', ' plc', 
                ' limited', ' & co.', 
                ' ab', ' a/s', ' oyj', ' asa', ' hf', ' abp', 
                ' incorporated', ' company', ' & company', 
                ' ag', ' (the)', ' and company', ' holdings', 
                ' financial', 'the ', ' corp', ' inc', ' hldgs', 
                ' companies', ' nl', ' se', 's.p.a.', ' spa', 's.a.', 
                'aktiengesellschaft', ', inc.', ' co. ltd.', 'ltd', 'plc'
                'company limited']

        self.QUERY_LIMIT = ""

        self._find_by_share_exact_name_query = ("SELECT s.name, s.isin FROM share s "
                "WHERE s.name = (%s) "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_exact_alias_query = ("SELECT s.name, s.isin "
                "FROM share s "
                "JOIN share_alias sa on sa.share = s.id "
                "WHERE sa.name = (%s) "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER +  self.QUERY_LIMIT)

        self._find_by_share_company_exact_name_query = ("SELECT s.name, s.isin " 
                "FROM share_company sc "
                "JOIN share s on s.share_company = sc.id "
                "WHERE sc.name = (%s) "
                + self.QUERY_WHERE_AND + 
                "GROUP BY sc.name "
                + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_company_fuzzy_name_query = ("SELECT s.name, s.isin " 
                "FROM share_company sc "
                "JOIN share s on s.share_company = sc.id "
                "WHERE sc.name like CONCAT('%', %s, '%') "
                + self.QUERY_WHERE_AND + 
                "GROUP BY sc.name "
                + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_fuzzy_name_query = ("SELECT s.name, s.isin " 
                "FROM share s "
                "WHERE s.name like CONCAT('%', %s, '%') "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_reverse_fuzzy_name_query = ("SELECT s.name, s.isin " 
                "FROM share s "
                "WHERE %s like CONCAT('%', s.name, '%') "
                "AND length(s.name) > 4 "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_fuzzy_alias_query = ("SELECT s.name, s.isin "
                "FROM share s "
                "JOIN share_alias sa on sa.share = s.id "
                "WHERE sa.name like CONCAT('%', %s, '%') "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_reverse_fuzzy_alias_query = ("SELECT s.name, s.isin "
                "FROM share s "
                "JOIN share_alias sa on sa.share = s.id "
                "WHERE %s like CONCAT('%', sa.name, '%') "
                "AND length(sa.name) > 4 "
                + self.QUERY_WHERE_AND + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._find_by_share_company_reverse_fuzzy_name_query = ("SELECT s.name, s.isin " 
                "FROM share_company sc "
                "JOIN share s on s.share_company = sc.id "
                "WHERE %s like CONCAT('%', sc.name, '%') "
                "AND length(s.name) > 4 "
                + self.QUERY_WHERE_AND + 
                "group by sc.name "
                + self.QUERY_ORDER + self.QUERY_LIMIT)

        self._exact_queries = (
            self._find_by_share_exact_name_query, 
            self._find_by_share_exact_alias_query)

            # not including SC in exact match. 
            #self._find_by_share_company_exact_name_query

        self._fuzzy_queries = (
            self._find_by_share_company_exact_name_query,
            self._find_by_share_company_fuzzy_name_query,
            self._find_by_share_fuzzy_name_query,
            self._find_by_share_reverse_fuzzy_name_query,
            self._find_by_share_fuzzy_alias_query,
            self._find_by_share_reverse_fuzzy_alias_query,
            self._find_by_share_company_reverse_fuzzy_name_query)

        # Search database
        self.connection = mysql.connector.connect(user='root', 
                                        password='root', 
                                        database='fondout')
        self.cursor = self.connection.cursor()

        # Update database
        self.cnxUp = mysql.connector.connect(
                                        user='root', 
                                        password='root', 
                                        database='fund_search')
        self.cursorUp = self.cnxUp.cursor()


    if __name__ == "__main__":
        # Parse args from command line
        parser = argparse.ArgumentParser()
        parser.add_argument("-f", "--fund", help="Fund to use.")
        args = parser.parse_args()

        # Prepare script to listen to ctrl-c
        signal.signal(signal.SIGINT, signal_handler)

        # --------- Choose selection of shares from database
        query_unidentified_shares = (
            "SELECT name from tmp_shareholding "
            "where isin IS NULL")
        # uncomment to enable Test list already caught share-isins / redo everything
        if args.fund is not None: 
            print "Fund used : ", args.fund
            query_unidentified_shares = (
                "SELECT name from tmp_shareholding "
                "where (select id from tmp_fund where name LIKE '%" 
                + args.fund + "%')"
                " AND isin IS NULL and false_positive = 0")

        self.cursorUp.execute(query_unidentified_shares)
        unidentifiedShares = self.cursorUp.fetchall()

        # --------------------------------------------------------------------------
        for (share_name,) in unidentifiedShares:
            # lowercase because MYSQL is not case sensitive, but python is. 
            share_name = share_name.lower()
            print "New share: ", share_name

            # --------------- Find section ---------------

            # First db-search attempt, exact match
            found_share = find_exact_share_routine(share_name)
            if (found_share is not None): 
                exact_match = True
            else: 
                exact_match = False

            # Second db-search attempt, fuzzy search
            if (found_share is None):
                found_share = find_share_routine(share_name)

            # Third db-search attempt with alternated name
            if (found_share is None):
                used_names = []
                used_names.append(share_name)
                for suffix in self.company_suffix:
                    new_name = share_name.replace(suffix, "")
                    if (new_name not in used_names):
                        found_share = find_share_routine(new_name)
                        used_names.append(new_name)
                    if (found_share is None):
                        new_name = new_name.replace(",", "").replace(".", "")
                        if (new_name not in used_names):
                            found_share = find_share_routine(new_name)
                            used_names.append(new_name)
                    if (found_share is None):
                        new_name = new_name.replace(" ", "")
                        if (new_name not in used_names):
                            found_share = find_share_routine(new_name)
                            used_names.append(new_name)
                    if (found_share is not None):
                        break

            if (exact_match is not True): 
                (googled_isin, 
                    googled_isin_matches, 
                    google_occurances) = isingoogle.search_google(share_name)
            else: 
                googled_isin_matches = 0
                google_occurances = None
                googled_isin = None

            if (found_share is not None):
                (found_name, found_isin) = found_share
                print found_name, found_isin
                if (googled_isin_matches > 0):
                    if (found_isin == googled_isin):
                        # CASE 1: GOOGLE = DBMATCH --> SAME ISIN
                        # Database found matches top google result
                        print 'Found isin matches googled isin, gr8 success!'
                        found_method = ("1: search and google match. Google hits: " 
                             + str(googled_isin_matches))
                    elif (googled_isin_matches > 3):
                        # CASE 2: GOOGLE(>3) != DBMATCH --> CHOOSE GOOGLE
                        # No match google hits wins - take google result
                        found_method = ("2:" + str(googled_isin_matches)
                            + " google hits, conflict search " + found_name + " "
                            + found_isin)
                        found_isin = googled_isin
                        found_name = "googled: "
                        result = share_by_isin(googled_isin)
                        if (result is not None):
                            found_name = found_name + ' ' + result[0]
                    elif (googled_isin_matches > 0):
                        if (found_isin in google_occurances): 
                            # CASE 3: GOOGLE(<3) != DBMATCH, DBMATCH in GOOG OCCURANC
                            # ---> CHOOSE DBMATCH
                            found_method = ("3: mismatch db. top google hit: " 
                                        + googled_isin + ":" + str(googled_isin_matches))
                        else: 
                            # CASE 4: GOOGLE(<4) != DBMATCH, DBMATCH NOT in GOOG OCCURANC

                            # found_isin = ""
                            # found_name = ""
                            found_method = ("4. mismatch db google(" 
                                + str(googled_isin_matches) + ") not in google results. "
                                + googled_isin)

                            result = share_by_isin(googled_isin)
                            if (result is not None):
                                found_method = (found_method + ' db-matched to ' + 
                                    result[0])
                
                elif (exact_match is True):
                    # CASE 8: EXACT MATCH         
                    found_method = "8: Exact match"
                else: 
                    # CASE 2: No google hits, but found in DB
                    # Make this a separate case?
                    found_method = "2: No google hits"


            elif (googled_isin_matches > 0):
                # min 3 google hits makes certain
                if (googled_isin_matches > 2):
                    found_isin = googled_isin
                    # Search current db for found isin.
                    result = share_by_isin(googled_isin)
                    found_method = ("5:" + str(googled_isin_matches) 
                                    + " results googled, faild db-search.")
                    found_name = "googled: "
                    if (result is not None):
                        found_name = found_name + result[0]
                else: 
                    found_method = ("6. Google hits: " + str(googled_isin_matches) 
                        + " : " + googled_isin)

                    result = share_by_isin(googled_isin)
                    if (result is not None):
                        found_method = found_method + ' db-matched to ' + result[0]

                    found_isin = ""
                    found_name = ""

            else:
                found_isin = ""
                found_name = ""
                found_method = "7: Nothing found!"

            # Get share_company
            found_share_company = ""
            if(found_isin != ""):
                found_share_company = share_company_by_isin(found_isin)

            
            # --------------- Update section ---------------
            query_update_isin = (
                "UPDATE tmp_shareholding "
                "SET matched_name=%s, isin=%s, method_found=%s, share_company=%s "
                "WHERE name = %s")

            update_share_values = (found_name, found_isin, found_method, 
                found_share_company, share_name)

            # If fund is specified add specific by fund.
            # obs: Should really be the exact one pulled from the database

            if args.fund is not None: 
                query_update_isin = (
                    query_update_isin + 
                    " and fund = (select id from tmp_fund "
                    "where name like CONCAT('%', %s, '%'))")
                update_share_values = update_share_values + (args.fund, )

            # Update share in fund_search where name = share_name
            try: 
                self.cursorUp.execute(query_update_isin, update_share_values)
            except Exception as e:
                print('Update execution error', e)

            # Use commit for confirming modification of data. 
            # Rollback to undo.

            # Disable for test. 
            self.cnxUp.commit() 
            time.sleep(randint(0,10))

        exit_procedure()
