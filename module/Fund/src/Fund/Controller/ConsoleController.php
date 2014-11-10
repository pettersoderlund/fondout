<?php
namespace Fund\Controller;

use SplFileObject;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Fund\Entity\ShareCompany;
use Fund\Entity\Source;
use Fund\Entity\AccusationCategory;
use Fund\Entity\Accusation;
use Fund\Entity\CarbonTracker;
use Fund\Entity\Fund;
use Fund\Entity\FundInstance;
use Fund\Entity\FundCompany;
use Fund\Entity\Shareholding;
use Fund\Entity\Share;
use Fund\Entity\BankFundListing;
use Fund\Entity\Emissions;

class ConsoleController extends AbstractActionController
{
    protected $consoleService;
    protected $em;

    public function mapsharecompaniesAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject(
            $this->getRequest()->getParam('csvfileIsinToSharecompany')
        );

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::DROP_NEW_LINE |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");
        // Skip header row
        $fileIterator = new \LimitIterator($file, 1);

        foreach ($fileIterator as $row) {
            $isin = $row[0];
            $shareCompanyName = $row[1];

            $this->mapIsinToShareCompany($shareCompanyName, $isin);

        }

    }

    public function mapIsinToShareCompany($name, $isin)
    {
        $service = $this->getConsoleService();
        // Get the entity manager straight up
        $em = $service->getEM();


        // Check if we have the sharecompany already

        // Get sharecompany
        $sharecompany = $em->getRepository('Fund\Entity\ShareCompany')
            ->findOneBy(['name' => $name]);
        //Get share
        $share = $em->getRepository('Fund\Entity\Share')
            ->findOneBy(['isin' => $isin]);

        // Create sharecompany if it does not already exist
        if (is_null($sharecompany)) {
            // Create the sharecompany missing
            $sharecompany = new ShareCompany();
            $sharecompany->setName($name);
            //Persist sharecompany
            $em->persist($sharecompany);
        }

        // Check if the share exists
        if (!$share) {
            echo "No share found with ISIN: $isin \n";

            // Maybe we should return an errorcode instead. throw exception?
            $em->clear();
            return null;
        }

        // Check if the share does not have a sharecompany already
        if (is_null($share->getShareCompany())) {
            $share->setShareCompany($sharecompany);
            //Persist share
            $em->persist($share);

        // If the share has a sharecompany, is it the same as the one given?
        } elseif ($share->getShareCompany()->getName() == $name) {
            echo "This share ($isin) $share->name already has the given "
             . "sharecompany set: $name \n";
            $em->clear();
            return null;
        } else {
            echo "WARNING: New share company set to: $share->name";
            echo "\n";
            echo "From " .  $share->shareCompany->name . " To $sharecompany->name \n";

            $share->setShareCompany($sharecompany);
            //Persist share
            $em->persist($share);
        }

        // Flush
        $em->flush();
        $em->clear();

    }

    public function addcompanyaccusationsAction()
    {
        $service = $this->getConsoleService();
        // Get the entity manager straight up
        $em = $service->getEM();

        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject(
            $request->getParam('companyAccusations')
        );

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::DROP_NEW_LINE |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");
        // Skip header row
        $fileIterator = new \LimitIterator($file, 1);

        $sourceRepo = $em->getRepository('Fund\Entity\Source');
        $scRepo = $em->getRepository('Fund\Entity\ShareCompany');
        $categoryRepo = $em->getRepository('Fund\Entity\AccusationCategory');

        $i = 0;
        $j = 0;
        foreach ($fileIterator as $row) {
            $shareCompany = $row[0];
            $accusation = $row[1];
            $category = $row[2];
            $source = $row[3];
            //echo var_dump($row) . "\n";

            $name = $source;
            $source = $sourceRepo->findOneBy(['name' => $source]);
            // Check if source exists
            if (is_null($source)) {
                echo "Source not found: $source $name\n";
                $j++;
                continue;
            }

            // Check if sharecompany exists
            $name = $shareCompany;
            $shareCompany = $scRepo->findOneBy(['name' => $shareCompany]);
            if (is_null($shareCompany)) {
                echo "Share company not found: $shareCompany $name\n";
                $j++;
                continue;
            }

            // Check category
            $name = $category;
            $category = $categoryRepo->findOneBy(['name' => $category]);
            if (is_null($category)) {
                echo "Category not found: $category $name\n";
                echo "Create this category manually if you'd like.\n";
                $j++;
                continue;
            }

            // Create share company accusation
            $scAccusation = new Accusation();
            $scAccusation->setSource($source);
            $scAccusation->setShareCompany($shareCompany);
            $scAccusation->setCategory($category);
            $scAccusation->setAccusation($accusation);


            $em->persist($scAccusation);
            $i++;
        }
        echo "$i accusation(s) successfully added.\n";
        echo "$j accusation(s) missed.\n";
        $em->flush();
        $em->clear();

    }

    public function addsourceAction()
    {
        $service = $this->getConsoleService();
        // Get the entity manager straight up
        $em = $service->getEM();

        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('sources'));

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::DROP_NEW_LINE |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");
        // Skip header row
        $fileIterator = new \LimitIterator($file, 1);
        $i = 0;
        $j = 0;

        foreach ($fileIterator as $row) {
            $name    = $row[0]; // Source name
            $url     = $row[1]; // Source url
            $date    = $row[2]; // Release date

            // Check date
            $timezone = "Europe/Stockholm";
            $datetimev = \DateTime::createFromFormat(
                'm/d/Y',
                $date,
                new \DateTimeZone($timezone)
            );
            // Set hours minutes seconds to 0/midnight
            $datetimev->setTime(0, 0, 0);

            $source = $em->getRepository('Fund\Entity\Source')->findOneByName($name);

            // Create or update source
            // Create source if it does not already exist
            if (is_null($source)) {
                // Create the sharecompany missing
                $source = new Source();
                $i++;

            } elseif ($source->releaseDate != $datetimev) {
                // If the date differs, create new source entry
                $source = new Source();
                $i++;
            } else {
                echo "Source: $name $date already exists\n";
                $j++;
                continue;
            }

            $source->setName($name);
            $source->setUrl($url);
            $source->setReleaseDate($datetimev);

            $em->persist($source);
            $em->flush();
            $em->clear();
        }
        echo "$j source(s) already exist(s).\n";
        echo "Imported $i new source(s).";
    }

    public function addcarbontrackerAction()
    {

        $service = $this->getConsoleService();
        // Get the entity manager straight up
        $entityManager = $service->getEM();

        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('file'));

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");

        // Start on second row
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $batchSize = 20;

        foreach ($fileIterator as $row) {
            $companyName = $row[0];
            $coal = $row[1];
            $oil = $row[2];
            $gas = $row[3];

            $shareCompany = $entityManager->getRepository('Fund\Entity\ShareCompany')
                ->findOneByName($companyName);
            $carbonTracker = $entityManager->getRepository('Fund\Entity\CarbonTracker')
                ->findOneByShareCompany($shareCompany);

            if (is_null($carbonTracker)) {
                $carbonTrackerEntry = new CarbonTracker();
            } else {
                $carbonTrackerEntry = $carbonTracker;
            }

            $carbonTrackerEntry->setCoal($coal);
            $carbonTrackerEntry->setGas($gas);
            $carbonTrackerEntry->setOil($oil);

            if (!is_null($shareCompany)) {
                $carbonTrackerEntry->setShareCompany($shareCompany);
            }

            $entityManager->persist($carbonTrackerEntry);

            if (($i++ % $batchSize) == 0) {
                $entityManager->flush();
                $entityManager->clear(); // Detaches all objects from Doctrine!
            }
        }
        $entityManager->flush();
        $entityManager->clear();
    }


    public function addfundAction()
    {

        echo "Be very precise on parameters. Always enter --date. \n"
          . "This action requires the use of php version >=5.4 \n";

        $service = $this->getConsoleService();
        // Get the entity manager straight up
        $entityManager = $service->getEM();

        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('file'));

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");

        // Fund fundamental information
        $fundamentalInfo = new \LimitIterator($file, 1, 5);

        $fundInfo = array();
        $i = 0;
        // Get fund. Should get info in the order:
        // [Name, ISIN, Fund company, Total AuM, Currency]
        foreach ($fundamentalInfo as $row) {
            $fundInfo[$i] = $row[1];
            $i++;
        }
        $fundName         = $fundInfo[0];
        $fundIsin         = $fundInfo[1];
        $fundCompanyName  = $fundInfo[2];
        $fundAuM          = $fundInfo[3];
        $fundCurrency     = $fundInfo[4];

        $fundAuM = str_replace(array(" ", ","), "", $fundAuM);


        echo $fundAuM . "\n";

        $exchangeRate = $request->getParam('exchangerate');
        $date = $request->getParam('date');

        echo var_dump($fundInfo);
        echo "\n";
        echo "xrate: $exchangeRate\n";
        echo "date: $date\n";

        // Fund company
        $fundCompany = $entityManager->getRepository('Fund\Entity\FundCompany')
            ->findOneByName($fundCompanyName);
        if (is_null($fundCompany)) {
            // Probably trouble
            // Fund company not found...
            // create a new one?
            $option = \readline(
                "No fund company found on $fundCompanyName \n" .
                "Please choose one from following options:\n" .
                "\t[1] Create new fundcompany:\t $fundCompanyName  \n" .
                "\t[2] Skip fund:\t\t\t " . $fundName . "\n"
            );

            switch ($option)
            {
                case 1:
                    echo "Creating new fund company: $fundCompanyName ...\n";
                    //Create new fund Company
                    $fundCompany = new FundCompany();
                    $fundCompany->setName($fundCompanyName);
                    $entityManager->persist($fundCompany);
                    break;

                case 2:
                    return "Skipped fund import of $fundName\n";
                    break;
            }

        }

        echo "Fundcompany step completed.\n";

        $fundInstance = null;

        // SEK / EUR?
        if ($fundCurrency  == "SEK") {
            $exchangeRate = 1;
        } else {
            if (is_null($exchangeRate)) {
                $exchangeRate = \readline(
                    "The currency for this fund is $fundCurrency \n" .
                    "Please enter the exchange rate to SEK for the correct date. \n" .
                    "Use . as a separation for decimals. (eg. 213.123)\n"
                );
            }

        }

        // Fund
        $fund = $entityManager->getRepository('Fund\Entity\Fund')
            ->findOneByIsin($fundIsin);

        // Is the fund already added?
        if (is_null($fund)) {
            // Create fund
            $fund = new Fund();
            // Create fund instance
            $fundInstance = new FundInstance();
        } else {
            // Fund obviously exists, fetch the instance
            $fundInstance = $entityManager
                ->getRepository('Fund\Entity\FundInstance')
                ->findOneByFund($fund);
        }

        // name, ISIN, \Fund\Entity\FundCompany
        $fund->setName($fundName);
        $fund->setUrl($this->createFundUrl($fundName));
        $fund->setIsin($fundIsin);
        $fund->setCompany($fundCompany);
        $entityManager->persist($fund);

        // Market value/AuM, Date, \Fund\Entity\Fund
        $fundInstance->setTotalMarketValue($fundAuM*$exchangeRate);
        $fundInstance->setFund($fund);

        // Check date
        $timezone = "Europe/Stockholm";
        if (is_null($date)) {
            $date = \readline(
                "Please enter date for the fund m/d/Y\n"
            );
        }

        $datetimev = \DateTime::createFromFormat(
            'm/d/Y',
            $date,
            new \DateTimeZone($timezone)
        );
        // Set hours minutes seconds to 0/midnight
        $datetimev->setTime(0, 0, 0);
        $fundInstance->setDate($datetimev);
        $entityManager->persist($fundInstance);

        // Shares
        // isin, name, Exchange rate, market_value
        $shares = new \LimitIterator($file, 9);

        $i = 0;
        $batchSize = 20;
        foreach ($shares as $row) {
            $isin = $row[0];
            $market_value = $row[1];
            $market_value = str_replace(array(" ", ","), "", $market_value);
            $name = $row[2];

            // Share exists?
            if (strlen($isin) > 10) {
                $share = $entityManager->getRepository('Fund\Entity\Share')
                    ->findOneByIsin($isin);
            } else {
                $share = $entityManager->getRepository('Fund\Entity\Share')
                    ->findOneByName($name);
            }

            if (is_null($share)) {
                $share = new Share();
                $share->setName($name);
                if (strlen($isin) > 5) {
                    $share->setIsin($isin);
                }
                $entityManager->persist($share);
            }


            // Shareholding exists?
            $shareHolding = $entityManager
                ->getRepository('Fund\Entity\Shareholding')->findOneBy(
                    array("fundInstance" => $fundInstance, "share" => $share)
                );

            if (is_null($shareHolding)) {
                $shareHolding = new ShareHolding();
                $shareHolding->setFundInstance($fundInstance);
                $shareHolding->setShare($share);
            }

            $shareHolding->setExchangeRate($exchangeRate);
            $shareHolding->setMarketValue($market_value*$exchangeRate);

            $entityManager->persist($shareHolding);

            if (($i++ % $batchSize) == 0) {
                $entityManager->flush();
                echo ".";
            }
        }

        $entityManager->flush();
        $entityManager->clear();  // Detaches all objects from Doctrine!
        echo " Success!\n";
    }

    public function addbanklistingAction()
    {

        $service = $this->getConsoleService();
        $entityManager = $service->getEM();
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('file'));
        $bankName = $request->getParam('bank');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");

        $fileIterator = new \LimitIterator($file, 0);

        // Funds added
        $i = 0;
        $batchSize = 1;

        // Funds with different names
        $j = 0;

        // New listing count
        $k = 0;

        // Found by name
        $l = 0;

        // Not found
        $m = 0;

        foreach ($fileIterator as $row) {
            $bank = $entityManager->getRepository('Fund\Entity\Bank')
                ->findOneByName($bankName);

            if (is_null($bank)) {
                return "Bank does not exist in database.\n Please add manually";
            }

            $fundName = $row[0];
            $isin = $row[1];
            //$url = $row[2];

            $fund = null;
            $fundByName = null;
            $bankListing = null;

            $fund = $entityManager->getRepository('Fund\Entity\Fund')
                ->findOneByIsin($isin);

            $fundByName = $entityManager->getRepository('Fund\Entity\Fund')
                ->findOneByName($fundName);

            // Prio on funds found by name contra isin search imported.
            if (!is_null($fundByName)) {
                $fund = $fundByName;
                $l++;
            }

            if (is_null($fund) && is_null($fundByName)) {
                // echo "No fund found by name or isin $isin $fundName\n";
                $m++;
                continue;
            }

            //$bankListing = $entityManager->getRepository('Fund\Entity\BankFundListing')
            //    ->findOneByFund($fund);
            $bankListing = $entityManager->getRepository('Fund\Entity\BankFundListing')
                ->findOneBy(array("fund" => $fund, "bank" => $bank));

            if (is_null($bankListing)) {
                $bankListing = new BankFundListing();
                $k++;
            }

            if (strcmp($fundName, $fund->getName()) !== 0) {
                echo "Names are not equal $fundName != $fund->name\n";
                $j++;
            }

            // echo "Adding fund $fund->name $isin to bank $bankName\n";


            $bankListing->setFund($fund);
            $bankListing->setBank($bank);
            //$bankListing->setUrl($url);

            $entityManager->persist($bankListing);

            if (($i++ % $batchSize) == 0) {
                $entityManager->flush();
                $entityManager->clear(); // Detaches all objects from Doctrine!
            }
        }

        $entityManager->flush();
        $entityManager->clear();

        echo $k . " new fundlistings added.\n";
        echo $i-$k . " fundlisting updated.\n";
        echo "$j funds differed on name.\n";
        echo "$l funds found by name.\n";
        echo "$m funds not found.\n";
    }


    public function addmarketcapAction()
    {

        $service = $this->getConsoleService();
        $entityManager = $service->getEM();
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('file'));

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");

        // Table has header rows
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $batchSize = 1;

        foreach ($fileIterator as $row) {

            $companyName = $row[0];
            $marketCap = $row[1];

            $company = $entityManager->getRepository('Fund\Entity\ShareCompany')
                ->findOneByName($companyName);

            if (is_null($company)) {
                echo "$companyName does not exist in database. " .
                     "Please add sharemap before adding market value\n";
                continue;
            }

            $company->setMarketValueSEK($marketCap);


            $entityManager->persist($company);

            if (($i++ % $batchSize) == 0) {
                $entityManager->flush();
                $entityManager->clear(); // Detaches all objects from Doctrine!
            }
        }

        $entityManager->flush();
        $entityManager->clear();

        echo "$i mkt cap values added.";
    }

    public function addemissionsAction()
    {

        $service = $this->getConsoleService();
        $entityManager = $service->getEM();
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject($request->getParam('file'));

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl("\t");

        // Table has header rows
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $batchSize = 1;

        foreach ($fileIterator as $row) {

            $companyName = $row[0];
            $scope1      = $row[1];
            $scope2      = $row[2];
            $scope12     = $row[3];
            $scope3      = $row[4];
            $year        = $row[5];

            $company = $entityManager->getRepository('Fund\Entity\ShareCompany')
                ->findOneByName($companyName);

            if (is_null($company)) {
                echo "$companyName does not exist in database. " .
                     "Please add sharemap before adding emissions\n";
                continue;
            }

            $emissions = $company->getEmissions();

            if (is_null($emissions)) {
                $emissions = new Emissions();
            }

            if (!$scope1 && !$scope2 && !$scope12  && !$scope3) {
                continue;
            }

            if ($scope1) {
                $emissions->setScope1($scope1);
            }
            if ($scope2) {
                $emissions->setScope2($scope2);
            }

            if (is_null($scope12) || $scope12 < 1) {
                if ($scope1 || $scope2) {
                    $emissions->setScope12($scope1+$scope2);
                }

            } else {
                $emissions->setScope12($scope12);
            }
            if ($scope3) {
                $emissions->setScope3($scope3);
            }


            if (!$year) {
                $year = 2013;
            }

            // Check date
            $date = "1/1/" . $year;
            $timezone = "Europe/Stockholm";
            $datetimev = \DateTime::createFromFormat(
                'm/d/Y',
                $date,
                new \DateTimeZone($timezone)
            );
            // Set hours minutes seconds to 0/midnight
            $datetimev->setTime(0, 0, 0);
            $emissions->setDate($datetimev);


            $emissions->setShareCompany($company);
            $entityManager->persist($emissions);

            if (($i++ % $batchSize) == 0) {
                $entityManager->flush();
                $entityManager->clear(); // Detaches all objects from Doctrine!
            }
        }

        $entityManager->flush();
        $entityManager->clear();

        echo "$i emissions added.";
    }

    private function createFundUrl($fundName)
    {
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
    }

    public function getConsoleService()
    {
        if (!$this->consoleService) {
            $this->consoleService = $this->getServiceLocator()->get('ConsoleService');
        }

        return $this->consoleService;
    }
}
