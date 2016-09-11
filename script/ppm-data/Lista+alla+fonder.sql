/* 
- https://secure.pensionsmyndigheten.se/NuvarandeOchTidigareFonder.html
- Hämta från filen Lista+alla+fonder
- Exportera till tab separated values
- Sätt rätt datum
- Importera v v v
*/

DROP TABLE temporary_table_lista_alla_fonder;
SELECT * FROM temporary_table_lista_alla_fonder WHERE active <> 1;


USE fondout_maxi;
SET @date = '2016-08-31';

CREATE TEMPORARY TABLE temporary_table_lista_alla_fonder LIKE fund;

LOAD DATA INFILE '/users/petter/Sites/fondout/script/ppm-data/Lista+alla+fonder+ (2).txt' 
IGNORE # will skip duplicate ISIN
INTO TABLE temporary_table_lista_alla_fonder 
CHARACTER SET latin1
FIELDS TERMINATED BY '\t'
LINES TERMINATED BY '\r' 
IGNORE 2 LINES # two first lines are headers
	(@FONDBOLAG, 
	ppm_id, 
	name, 
	@VALUTA, 
	@FONDAVGIFT, 
	@BRUTTO_AVG, 
	@TKA, 
	@KATEGORI, 
	isin, 
	@fof, 
	@RESULTATBEROENDE, 
	@SLOW_, 
	@SPREAD, 
	@miljoetisk, 
	@FORLANGD, 
	@STARTDATUM, 
	@AVSLUTSDATUM, 
	@FONDSTATUS)

SET 
	pm_date 	= @date,
	swesif 	 	= IF (@miljoetisk = 'J', 1, 0),
    fof		 	= IF (@fof = 'J', 1, 0),
    annual_fee	= REPLACE(@BRUTTO_AVG, ',', '.'), #CAST(@BRUTTO_AVG AS DECIMAL(8,2)), #
    url 		= '',  # set to suppress warnings, dummy value
    active 		= IF(@FONDSTATUS <> 1, -1, 1)    # Används för att skilja ut vilka vi ska importera, status 1 aktiva fonder. 
													# 1.     Valbara fonder
													# 4, 8.  Ej valbara fonder
													# 5.      Avregistrerade
;

UPDATE 
	fund dest, 
	temporary_table_lista_alla_fonder src
SET 
	dest.ppm_id = src.ppm_id, 
    dest.swesif = src.swesif,
    dest.fof = src.fof,
    dest.pm_date = src.pm_date,
    dest.annual_fee = src.annual_fee
WHERE 
	dest.isin = src.isin AND src.active = 1;
/*
SELECT * FROM temporary_table_lista_alla_fonder where swesif = 1;
SELECT * FROM fund where swesif = 1;
*/
DROP TABLE temporary_table_lista_alla_fonder;
