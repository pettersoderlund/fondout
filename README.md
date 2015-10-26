Fondout (www.sparahallbart.se)
=======================

[![wercker status](https://app.wercker.com/status/38da5829a241418599dc1709c21adcb4/m "wercker status")](https://app.wercker.com/project/bykey/38da5829a241418599dc1709c21adcb4)


Installing the application:

Requirements:
- php 5.4=<
- MYSQL
- npm
- composer
- bower
- grunt


pull repository

$npm install && composer install && bower install && grunt less


DATABASE
--------
For development database should be named 'fondout_maxi', which holds fund data for a rage of dates.

For production a minified fondout database should be used, this database is called 'fondout'. This is created through a script in the scripts folder, export_minimize_db (usage $sh export_minimize_db). To use this file script/dbconfig.local must be set with the according credentials. A template is provided in dbconfig.local.dist.

Additionally to run the application config files for zf2 should be set in config/autoload/local.php where a template for which values are needed is provided in local.php.dist.


DOCUMENTS
---------
PDF documents are currently on the openshift server stored in the $OPENSHIFT_DATA_DIR. public/documents is therefore in the gitignore and symlinked in a actionhook on the openshift server.
Files used today are referenced from sparahallbart.se/products for price lists and product sheets.
files are uploaded to openshift staging and production server through e.g.
rhc scp --app stage upload -f Sites/fondout/public/documents/price-list-sparahallbart-se.pdf -r app-root/data
