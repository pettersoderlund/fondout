Proceduren med en ny FI-lista 
----------------------------------------

1. Ta bort shareholdings samt fundinstances: 
use fondout; set_foreign_key_checks=0; truncate shareholding; truncate fund_instance;

2. Importera
/Applications/MAMP/bin/php/php5.5.10/bin/php Scripts/fi_data_import.php [fi-listan.txt]

3. Klart ... ? 