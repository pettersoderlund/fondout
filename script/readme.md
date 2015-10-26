---- Proceduren med en ny FI-lista -----
----------------------------------------

Gamla sättet där vi bara hade en fund_instance åt gången:
1. Ta bort shareholdings samt fundinstances:
use fondout; set_foreign_key_checks=0; truncate shareholding; truncate fund_instance;

2. Importera
/Applications/MAMP/bin/php/php5.5.10/bin/php Scripts/fi_data_import.php [fi-listan.txt]

3. Klart ... ?

nya sättet:

1. php script php-data-import [fi-listan.txt] [databas=fondout_maxi]


-------- Post import procedures --------
---------------------------------------

4. Importera share_company mappings igen - det kan ha kommit in nya värdepapper.

5. Identifiera fof på nya fonder

6. Sätt kategorier och active på nya fonder

7. Sätt isin på nya fonder

RÄkna ut nyckeltal
8. php public/index.php update fund-measures

Exportera och backup
9. sh script/export_minimize_db && sh script/database_backup

10. I vissa fall (WWF grön 100) där tex ABB är med bör man "klumpa" ABB-
aktierna alternativt länka ALLA abb-aktier (även de utan isin) till
sharecompany ABB.


Om felet
PHP Fatal error:  Class 'NumberFormatter' not found in /Users/petter/Sites/fondout/script/fi_data_import.php on line 67
Kommer beror det oftast på att intl inte är installerat eller aktiverat i php.ini.
