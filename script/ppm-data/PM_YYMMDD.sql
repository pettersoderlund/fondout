/* 
- http://www.pensionsmyndigheten.se/FondManadsstatistik.html
- http://www.pensionsmyndigheten.se/FondManadsstatistikArkiv.html
- Hämta från filen PM+YYMMDD.xlsx, flik Fondstatistik. 
- Klipp över i mallen
- Save as tab delimited text 
*/
USE fondout_maxi;
SET @date = '2016-03-31';
DROP TABLE temporary_table;
CREATE TEMPORARY TABLE temporary_table LIKE fund;
SELECT * FROM temporary_table;
LOAD DATA INFILE '~/Sites/fondout/script/ppm-data/PM_YYMMDD-import-mall.txt' IGNORE
INTO TABLE temporary_table
CHARACTER SET latin1
FIELDS TERMINATED BY '\t'
LINES TERMINATED BY '\r' 
IGNORE 2 LINES # two first lines are headers
(name, 
@Iar,
@3man,
@6man,
nav1year, #@12man,
nav3year, #@36man,
nav5year, #@60man,
@Snitt5ar,
@Netto,
@BruttoTER,
@Risk,
@sharpekvot,
@valuta,
@Startdatum,
@Utlandsk,
ppm_id, #@Fondnummer,
@Forvaltare,
info, #@Fondkategori,
@Etisk)


SET 
	#annual_fee 	 = REPLACE(@fee, ',', '.'),
	pm_date 	= @date,
	url 		= '',  # set to suppress warnings, dummy value
    active 		= 0    # set to suppress warnings, dummy value

;   
# ----------------------------------------------------------------------------

UPDATE 

fund, temporary_table
SET fund.nav1year	= temporary_table.nav1year, 
fund.nav3year 		= temporary_table.nav3year, 
fund.nav5year		= temporary_table.nav5year, 
#fund.annual_fee		= temporary_table.annual_fee, 
fund.pm_date 		= temporary_table.pm_date
WHERE
temporary_table.ppm_id = fund.ppm_id;
# ---- Kontrollera om det finns gamla data
#SELECT isin, nav1year, nav3year, nav5year, annual_fee, pm_date FROM temporary_table;
SELECT  * from fund where pm_date != "2015-12-31";
#SELECT  * from fund;
DROP TABLE temporary_table;

# ----- Ta bort gammal data
update fund set nav1year = null, nav3year=null, nav5year=null  where pm_date is null ;

