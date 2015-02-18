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

    TODO: Snygga upp queries, slå ihop alla liknande find_by funktioner. 
"""

import sys
import mysql.connector
import isingoogle

def find_by_share_exact_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT name, isin FROM share "
        "WHERE name = (%s) AND isin IS NOT NULL "
        "AND (share.category is null or share.category = 1) "
        "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share


def find_by_share_company_exact_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT sc.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE sc.name = (%s) "
            "AND s.isin IS NOT NULL "
            "AND (s.category = 1 or s.category is null) "
            "GROUP BY sc.name "
            "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share

def find_by_share_company_fuzzy_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT sc.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE sc.name like CONCAT('%', %s, '%') "
            "AND s.isin IS NOT NULL "
            "AND (s.category = 1 or s.category is null) "
            "GROUP BY sc.name "
            "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share

def find_by_share_fuzzy_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT s.name, s.isin " 
            "FROM share s "
            "WHERE s.name like CONCAT('%', %s, '%') "
            "AND s.isin IS NOT NULL "
            "AND (s.category = 1 or s.category is null) "
            "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share

def find_by_share_reverse_fuzzy_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT s.name, s.isin " 
            "FROM share s "
            "WHERE %s like CONCAT('%', s.name, '%') "
            "AND length(s.name) > 4 "
            "AND s.isin IS NOT NULL "
            "AND (s.category = 1 or s.category is null) "
            "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share

def find_by_share_company_reverse_fuzzy_name(connection, name):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT s.name, s.isin " 
            "FROM share_company sc "
            "JOIN share s on s.share_company = sc.id "
            "WHERE %s like CONCAT('%', sc.name, '%') "
            "AND length(s.name) > 4 "
            "AND s.isin IS NOT NULL "
            "AND (s.category = 1 or s.category is null) "
            "group by sc.name "
            "order by s.id asc "
            "LIMIT 1")
    try:
        cursor.execute(query, (name, ))
    except mysql.connector.errors.IntegrityError, e: 
        print ('Find share by name exception:', e)
    if (cursor is not None):
        share = cursor.fetchone()
    else:
        share = None

    cursor.close()
    return share

def share_by_isin(connection, isin):
    cursor = connection.cursor(buffered=True)
    query = ("SELECT name FROM share "
        "WHERE isin = (%s) "
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

def find_share_routine(name):
    share = None

    share = find_by_share_exact_name(cnx, name)
    if (share is None):
        share = find_by_share_company_exact_name(cnx, name)
    
    # Do not allow fuzzy search on search strings shorter than four letters!
    if (len(name) > 3):
        if (share is None): 
            share = find_by_share_fuzzy_name(cnx, name)
        if (share is None): 
            share = find_by_share_company_fuzzy_name(cnx, name)
        if (share is None): 
            share = find_by_share_reverse_fuzzy_name(cnx, name)
        if (share is None): 
            share = find_by_share_company_reverse_fuzzy_name(
                cnx, name)
    return share

# --------------------------------------------------------------------------
# Search database
cnx = mysql.connector.connect(user='root', 
                                password='root', 
                                database='fondout')
cursor = cnx.cursor()

# Update database
cnxUp = mysql.connector.connect(
                                user='root', 
                                password='root', 
                                database='fund_search')
cursorUp = cnxUp.cursor()


company_suffix =[' group', '.', ',', ' corporation', ' group', ' plc', 
                ' limited', ' & co.', 
                ' ab', ' a/s', ' oyj', ' asa', ' hf', ' abp', 
                ' incorporated', ' company', ' & company', 
                ' ag', ' (the)', ' and company', ' holdings', 
                ' financial', 'the ', ' corp', ' inc', ' hldgs', 
                ' companies', ' nl', ' se', 's.p.a.', ' spa', 's.a.', 
                'aktiengesellschaft', ', inc.', ' co. ltd.']

query_unidentified_shares = (
    "SELECT name from tmp_shareholding "
    "where isin IS NULL")
# uncomment to enable Test list already caught share-isins / redo everything
# query_unidentified_shares = ("SELECT name from tmp_shareholding ")

cursorUp.execute(query_unidentified_shares)
unidentifiedShares = cursorUp.fetchall()

# --------------------------------------------------------------------------
for (share_name,) in unidentifiedShares:
    # lowercase because MYSQL is not case sensitive, but python is. 
    share_name = share_name.lower()
    print "New share: ", share_name

    # --------------- Find section ---------------

    found_share = find_share_routine(share_name)

    if (found_share is None):
        used_names = []
        used_names.append(share_name)
        for suffix in company_suffix:
            #print share_name.replace(suffix, "")
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

    (googled_isin, 
        googled_isin_matches, 
        google_occurances) = isingoogle.search_google(share_name)

    if (found_share is not None):
        (found_name, found_isin) = found_share
        print found_name, found_isin
        if (googled_isin is not None):
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
                    + " google hits, conflict search" + found_name 
                    + found_isin)
                found_isin = googled_isin
                found_name = "googled: "
                result = share_by_isin(cnx, googled_isin)
                if (result is not None):
                    found_name = found_name + ' matched to ' + result[0]
            elif (google_occurances is not None):
                if (found_isin in google_occurances): 
                    # CASE 3: GOOGLE(<3) != DBMATCH, DBMATCH in GOOG OCCURANC
                    # ---> CHOOSE DBMATCH
                    found_method = ("3: mismatch db. top google hit: " 
                                + googled_isin + ":" + str(googled_isin_matches))
                else: 
                    # CASE 4: GOOGLE(<3) != DBMATCH, DBMATCH NOT in GOOG OCCURANC
                    found_isin = ""
                    found_name = ""
                    found_method = ("4. mismatch db google(" 
                        + str(googled_isin_matches) + ") not in google results."
                        + googled_isin)

                    result = share_by_isin(cnx, googled_isin)
                    if (result is not None):
                        found_method = found_method + ' db-matched to ' + result[0]

    elif (googled_isin is not None):
        # min 3 google hits makes certain
        if (googled_isin_matches > 2):
            found_isin = googled_isin
            # Search current db for found isin.
            result = share_by_isin(cnx, googled_isin)
            found_method = ("5:" + str(googled_isin_matches) 
                            + " results googled, faild db-search.")
            found_name = "googled: "
            if (result is not None):
                found_name = found_name + result[0]
        else: 
            found_method = ("6. Google hits: " + str(googled_isin_matches) 
                + " : " + googled_isin)

            result = share_by_isin(cnx, googled_isin)
            if (result is not None):
                found_method = found_method + ' db-matched to ' + result[0]

            found_isin = ""
            found_name = ""

    else:
        found_isin = ""
        found_name = ""
        found_method = "7: Nothing found!"

    
    # --------------- Update section ---------------
    query_update_isin = (
        "UPDATE tmp_shareholding "
        "SET matched_name=%s, isin=%s, method_found=%s "
        "WHERE name = %s")

    update_share_values = (found_name, found_isin, found_method, share_name)
    # Update share in fund_search where name = share_name
    try: 
        cursorUp.execute(query_update_isin, update_share_values)
        #print cursorUp
    except Exception as e:
        print('Update execution error', e)
    
# Use commit for confirming modification of data. 
# Rollback to undo.

# Disable for test. 
cnxUp.commit() 

cursorUp.close()
cnxUp.close()

cursor.close()
cnx.close()
