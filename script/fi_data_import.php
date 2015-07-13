<?php

// Utility function
$createFundUrl = function ($fundName) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $fundName);
    // trim
    $text = trim($text, '-');
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // lowercase
    $text = strtolower($text);
    // remove unwanted characters
    return preg_replace('~[^-\w]+~', '', $text);
};

// Open CSV file
$file = new SplFileObject($argv[1]);
$file->setFlags(
    SplFileObject::READ_CSV |
    SplFileObject::DROP_NEW_LINE |
    SplFileObject::READ_AHEAD |
    SplFileObject::SKIP_EMPTY
);
$file->setCsvControl("\t");
// Skip header row
$fileIterator = new LimitIterator($file, 1);

// Create db connection
$dbname = $argv[2];
$dsn  = 'mysql:host=' . '127.0.0.1' . ';';
$dsn .= 'dbname=' . $dbname . ';';
$dsn .= 'charset=' . 'utf8' . ';';
$db = new \PDO(
    $dsn,
    'root',
    'root',
    array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);


// Print file info
$a = 0;
$b = 0;

foreach ($fileIterator as $row) {
    $type = $row[0];

    if (strcasecmp($type, 'info') == 0) {
        ++$a;
    } elseif (strcasecmp($type, 'data') == 0) {
        ++$b;
    } else {
        print implode(', ', $row);
    }
}

print "\nTotal info rows: " . $a;
print "\nTotal data rows: " . $b;
print "\n";

$i = 0;
$currentFundInstance = null;

// NOTE: Requires php5-intl
$fmt = new \NumberFormatter('sv_SE', \NumberFormatter::DECIMAL);
$fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 15);

$shares = array();
try {
    $db->beginTransaction();

    // Insert shares
    foreach ($fileIterator as $row) {
        $row = array_pad($row, 18, '');
        list (
            $type,                      // Posttyp
            $date,                      // Kvartalsslut
            $fundCompanyInstitutionNbr, //Institutnrfondbolag
            $fundCompanyName,           // Firma_fondbolag
            $fundInstitutionNbr,        // Institutnr_fond
            $fundName,                  // Firma_fond
            $marketValueTotal,          // Marknadsvarde_tot
            $capital,                   // Fondformogenhet
            $nav,                       // Andelsvarde
            $shareName,                 // Instrumentnamn
            $isin,                      // ISIN
            $country,                   // Land
            $quantity,                  // Antal_instr
            $interestRate,              // Kurs_ranta
            $exchangeRate,              // Valutakurs
            $marketValue,               // Marknadsvarde
            $unlisted,                  // Onoterad
            $status                     // Inlanad_Utlanad
        ) = $row;

        $fundName = utf8_encode($fundName);
        $fundCompanyName = utf8_encode($fundCompanyName);
        $shareName = utf8_encode($shareName);

        if (strcasecmp($type, 'data') == 0) {
            if (trim($isin) == '') {
                $sql  = "INSERT INTO share (id, name, isin, country_code) ";
                $sql .= "VALUES (?, ?, NULL, ?) ON DUPLICATE KEY UPDATE name = ?";

                $stmt = $db->prepare($sql);
                $stmt->execute(
                    [ 'NULL',
                      $shareName,
                      $country,
                      $shareName ]
                );
            } else {
                $sql  = "INSERT INTO share (id, name, isin, country_code) ";
                $sql .= "VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = ?, isin = ?";

                $stmt = $db->prepare($sql);
                $stmt->execute(
                    [ 'NULL',
                      $shareName,
                      $isin,
                      $country,
                      $shareName,
                      $isin ]
                );
            }
        } else {
            $i++;

            if (($i % 10) == 0) {
                print '.';
            }
        }
    }

    // What is the variable ids??
    // print count($ids);

    // insert fund companies, funds, fund instances & shareholdings
    foreach ($fileIterator as $row) {
        $row = array_pad($row, 18, '');
        list (
            $type,                      // Posttyp
            $date,                      // Kvartalsslut
            $fundCompanyInstitutionNbr, //Institutnrfondbolag
            $fundCompanyName,           // Firma_fondbolag
            $fundInstitutionNbr,        // Institutnr_fond
            $fundName,                  // Firma_fond
            $marketValueTotal,          // Marknadsvarde_tot
            $capital,                   // Fondformogenhet
            $nav,                       // Andelsvarde
            $shareName,                 // Instrumentnamn
            $isin,                      // ISIN
            $country,                   // Land
            $quantity,                  // Antal_instr
            $interestRate,              // Kurs_ranta
            $exchangeRate,              // Valutakurs
            $marketValue,               // Marknadsvarde
            $unlisted,                  // Onoterad
            $status                     // Inlanad_Utlanad
        ) = $row;

        // string to utf-8
        $fundName         = utf8_encode($fundName);
        $fundCompanyName  = utf8_encode($fundCompanyName);
        $shareName        = utf8_encode($shareName);

        // string to decimal
        $marketValueTotal = $fmt->parse($marketValueTotal);
        $capital          = $fmt->parse($capital);
        $nav              = $fmt->parse($nav);
        $quantity         = $fmt->parse($quantity);
        $interestRate     = $fmt->parse($interestRate);
        $exchangeRate     = $fmt->parse($exchangeRate);
        $marketValue      = $fmt->parse($marketValue);

        if (strcasecmp(trim($type), 'info') == 0) {

            $skip = false;
            $sql  = "INSERT INTO fund_company(id, institution_number, name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [ 'NULL',
                  $fundCompanyInstitutionNbr,
                  $fundCompanyName,
                  $fundCompanyName]
            );

            $sql  = "INSERT INTO fund (id, company, institution_number, url, name) ";
            $sql .= "SELECT ?, id, ?, ?, ? FROM fund_company WHERE institution_number = ? ";
            $sql .= "ON DUPLICATE KEY UPDATE name = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [ 'NULL',
                  $fundInstitutionNbr,
                  $createFundUrl($fundName),
                  $fundName,
                  $fundCompanyInstitutionNbr,
                  $fundName ]
            );

            $sql  = "INSERT IGNORE INTO fund_instance (id, fund, date, total_market_value, net_asset_value) ";
            $sql .= "SELECT ?, id, ?, ?, ? FROM fund WHERE institution_number = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [ 'NULL',
                  $date,
                  $marketValueTotal,
                  $nav,
                  $fundInstitutionNbr ]
            );

            $lastInsertId = $db->lastInsertId();

            if ($lastInsertId != 0) {
                $currentFundInstance = $lastInsertId;
            } else {
                $skip = true;
                echo "skipping $fundName \n";
            }

            $i++;

            if (($i % 10) == 0) {
                print '.';
            }


        } elseif (strcasecmp(trim($type), 'data') == 0 && !$skip) {
            $sql  = "INSERT INTO shareholding (id, fund_instance, share, ";
            $sql .= "market_value) ";
            $sql .= "SELECT DISTINCT ?, ?, id, ? FROM share ";
            $sql .= "WHERE " . ((trim($isin) == '') ? 'name = ? AND isin IS NULL' : 'isin = ?') . " LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [ 'NULL',
                  $currentFundInstance,
                  $marketValue,
                  ((trim($isin) == '') ? $shareName : $isin) ]
            );
        }
    }
    $db->commit();
} catch (PDOException $e) {
    // Rollback to initial state on error
    print "\n";
    print $e->getMessage();
    print "\nUnable to import " . $argv[1] . " ROLLING BACK!\n";
    $db->rollback();
    die;
}

print "\nFI data import complete.\n";
