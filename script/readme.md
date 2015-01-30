---- Proceduren med en ny FI-lista -----
----------------------------------------

1. Ta bort shareholdings samt fundinstances: 
use fondout; set_foreign_key_checks=0; truncate shareholding; truncate fund_instance;

2. Importera
/Applications/MAMP/bin/php/php5.5.10/bin/php Scripts/fi_data_import.php [fi-listan.txt]

3. Klart ... ? 


-------- Post import procedures --------
----------------------------------------
4. Importera share_company mappings igen - det kan ha kommit in nya värdepapper. 

5. Identifiera fof på nya fonder

6. Sätt kategorier på nya fonder

10. I vissa fall (WWF grön 100) där tex ABB är med bör man "klumpa" ABB-
aktierna alternativt länka ALLA abb-aktier (även de utan isin) till 
sharecompany ABB.