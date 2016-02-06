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
use Fund\Entity\StockExchangeListing;
use Fund\Entity\Sector;
use Fund\Entity\Industry;
use Fund\Entity\CompanyAlias;
use Fund\Entity\ShareAlias;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;



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
            ->findOneBy(array('name' => $name));
        //Get share
        $share = $em->getRepository('Fund\Entity\Share')
            ->findOneBy(array('isin' => $isin));

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
            // echo "No share found with ISIN: $isin \n";

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
            /*echo "This share ($isin) $share->name already has the given "
             . "sharecompany set: $name \n";*/
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
            $source = $sourceRepo->findOneBy(array('name' => $source));
            // Check if source exists
            if (is_null($source)) {
                echo "Source not found: $source $name\n";
                $j++;
                continue;
            }

            // Check if sharecompany exists
            $name = $shareCompany;
            $shareCompany = $scRepo->findOneBy(array('name' => $shareCompany));
            if (is_null($shareCompany)) {
                echo "Share company not found: $shareCompany $name\n";
                $j++;
                continue;
            }

            // Check category
            $name = $category;
            $category = $categoryRepo->findOneBy(array('name' => $category));
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
            } else {
              echo "WARNING: Sharecompany $companyName not found!\n";
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

        echo "AuM: ". $fundAuM . $fundCurrency . "\n";

        $exchangeRate       = $request->getParam('exchangerate');
        $date               = $request->getParam('date');
        // Double holdings means that a fund has several holdings of the
        // same security (same isin)
        $doubleHoldings     = $request->getParam('doubleholdings');
        $smallBatch         = $request->getParam('smallbatch');

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
                    $fundCompany->setUrl($this->createFundUrl($fundCompanyName));
                    $entityManager->persist($fundCompany);
                    break;

                case 2:
                    return "Skipped fund import of $fundName\n";
                    break;
            }

        }

        echo "Fundcompany step completed.\n";

        // Check date
        $timezone = "Europe/Stockholm";
        if (is_null($date)) {
            $date = \readline(
                "Please enter date for the fund m/d/Y\n"
            );
        }

        do  {
            $datetimev = \DateTime::createFromFormat(
                'm/d/Y',
                $date,
                new \DateTimeZone($timezone)
            );

            if(!$datetimev) {
                $date = \readline(
                            "Date: $date entered. " .
                            "Please enter date for the fund m/d/Y\n"
                            );
            }

        } while (!$datetimev);

        // Set hours minutes seconds to 0/midnight
        $datetimev->setTime(0, 0, 0);

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
            $fundInstance = new FundInstance();
        } else {
          $fundInstance = $entityManager
              ->getRepository('Fund\Entity\FundInstance')
              ->findOneBy(array('fund' => $fund, 'date' => $datetimev));
          if(is_null($fundInstance)){
              // Create fund instance
              $fundInstance = new FundInstance();
          }
        }

        if ($doubleHoldings) {
            echo "Using doubleholdings flag the shareholdings should be empty. \n";
            echo "Printing shareholdings:\n" ;
            echo \Doctrine\Common\Util\Debug::dump(
                    $fundInstance->getShareholdings());
            if (sizeof($fundInstance->getShareholdings()) != 0) {
                echo "Share has shareholdings, quitting to prevent disaster.\n" .
                 "Remove all shareholdings to manually to complete this action\n";
                exit(0);
            } else {
                echo "No shareholdings, ok.\n";
            }
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

        $fundInstance->setDate($datetimev);
        $entityManager->persist($fundInstance);

        // Shares
        // isin, name, Exchange rate, market_value
        $shares = new \LimitIterator($file, 9);

        $i = 0;
        $batchSize = 20;
        if($doubleHoldings || $smallBatch) {
            $batchSize = 1;
        }

        foreach ($shares as $row) {
            $isin = $row[0];
            $market_value = $row[1];
            # if one , replace with .
            # if more than one , replace with ""
            $market_value = str_replace(" ", "", $market_value);

            if(is_string($market_value)) {
              if (substr_count($market_value, ",") > 1) {
                $market_value = str_replace(",", "", $market_value);
              } elseif (substr_count($market_value, ",") == 1) {
                $market_value = str_replace(",", ".", $market_value);
              }
              if (substr_count($market_value, ".") > 1) {
                $market_value = str_replace(".", "", $market_value);
              }
            }

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
            } else {
                $shareAlias = $entityManager
                    ->getRepository('Fund\Entity\ShareAlias')
                    ->findOneByName($name);
                if (is_null($shareAlias)) {
                    $shareAlias = new ShareAlias();
                    $shareAlias->setName($name);
                }
                $shareAlias->setShare($share);
                $entityManager->persist($shareAlias);
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

            if(!$doubleHoldings) {
                $shareHolding->setMarketValue($market_value*$exchangeRate);
            } else {
                $shareHolding->setMarketValue(
                        $market_value*$exchangeRate
                        + $shareHolding->getMarketValue()
                        );
            }


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
                $scope1 = str_replace(',', '', $scope1);
                $emissions->setScope1($scope1);
            }

            if ($scope2) {
                $scope2 = str_replace(',', '', $scope2);
                $emissions->setScope2($scope2);
            }

            if (is_null($scope12) || $scope12 < 1) {
                if ($scope1 || $scope2) {
                    $emissions->setScope12($scope1+$scope2);
                }

            } else {
                $scope12 = str_replace(',', '', $scope12);
                $emissions->setScope12($scope12);
            }

            if ($scope3) {
                $scope3 = str_replace(',', '', $scope3);
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


    /**
    *   This method is an attempt for a generic way to identify sharecomapnies
    *   we've got in the DB or not. The goal is for it to split the entered file
    *   giving four files:
    *
    *   - one with the matched entries and
    *   - one where the entrie sdoes not match to know which ones to research
    *      and find new sharemaps :)
    *   - One with parital name matches
    *   - One with companies found in the partial and exact matches w/ mkt cap
    *
    *
    *   First row have headers.
    *
    **/
    public function matchcompaniesAction()
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

        // Company name column, defaults to 0
        $companyNameColumn = $request->getParam('company-name-column');
        $outputDir = $request->getParam('output-directory');
        $delimiter = $request->getParam('delimiter');
        $mktCapOption =
          $request->getParam('market-cap') || $request->getParam('m');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl($delimiter);

        // Table has header rows
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $j = 0;
        $h = 0;

        // Companies not in DB
        $newFile = fopen($outputDir . '/' . 'newCompanies.tsv', 'w', chr(9));
        // Companies found on exact match
        $existingFile = fopen($outputDir . '/' . 'existing.tsv', 'w', chr(9));
        // Companies found on partial match
        $maybeExistingFile = fopen($outputDir . '/' . 'maybe-existing.tsv', 'w', chr(9));

        if ($mktCapOption) {
          // Market cap on companies found, both exact and partial
          $existingMktCapFile = fopen($outputDir . '/' . 'existing-w-mkt-cap.tsv', 'w', chr(9));
        }

        // Add headers
        foreach (new \LimitIterator($file, 0, 1) as $header) {
            fputcsv($newFile, $header, chr(9));
            fputcsv($existingFile, $header, chr(9));
            fputcsv($maybeExistingFile, $header, chr(9));
        }

        if ($mktCapOption) {
          fputcsv(
            $existingMktCapFile,
            array(
              'Company Name (sharecompany)',
              'Market cap SEK'
            ),
            chr(9));
        }

        foreach ($fileIterator as $row) {
            $companyName = $row[$companyNameColumn];

            $company = $entityManager->getRepository('Fund\Entity\ShareCompany')
                ->findOneByName($companyName);

            if (is_null($company)) {
              // Simplify LIKE search string
              $companySuffix = $this->getCompanySuffix();
              $companyName = strtolower($companyName);
              $trimmedCompanyName = str_replace($companySuffix, "", $companyName);
              $trimmedCompanyName = trim($trimmedCompanyName);

              /*if ($companyName == "The AES Corporation") {
                echo "HELLO!";
                echo $trimmedCompanyName;
              }*/

              $result = $entityManager->getRepository('Fund\Entity\ShareCompany')
               ->createQueryBuilder('o')
               ->where('o.name LIKE :name')
               ->setParameter('name', '%' . $trimmedCompanyName .'%')
               ->getQuery()
               ->getResult();

               if (sizeof($result) > 0) {
                 /*echo $result[0]->name . " is this what youre looking for? "
                 . $companyName . "\n";*/
                 if ($mktCapOption) {
                   fputcsv($existingMktCapFile, array($result[0]->name, $result[0]->marketValueSEK), chr(9));
                 }

                 $row[] = $result[0]->name;
                 $row[] = ($result[0]->date) ? "HAS DATE" : "HAS NOT GOT DATE";

                 fputcsv($maybeExistingFile, $row, chr(9));
                 $h++;
                 continue;
               }
            }

            if (is_null($company)) {
                $i++;

                // this company is to add to the file listing
                // the companies not in DB.
                fputcsv($newFile, $row, chr(9));
                /*echo "$companyName does not exist in database.\n " .
                     "Please add sharemaps \n";*/
                continue;

            } else {
                $j++;

                // This line should be added to companies found file.
                // echo "$companyName found! ====> $company->name\n";
                fputcsv($existingFile, $row, chr(9));

                // print this to another file.
                // Set if to print this in an option to console route?
                // echo "$company->name market cap: $company->marketValueSEK \n";
                if ($mktCapOption) {
                  fputcsv($existingMktCapFile, array($company->name, $company->marketValueSEK), chr(9));
                }

                continue;
            }
        }

        fclose($newFile);
        fclose($existingFile);
        fclose($maybeExistingFile);

        if ($mktCapOption) {
          fclose($existingMktCapFile);
        }

        echo "$i new companies.\n";
        echo "$j existing companies.\n";
        echo "$h maybe existing companies. (partial matches)\n";
    }

    /**
    *
    *  This method is to find ISIN numbers from our current DB of shares to
    *  map them to sharecompanies.
    *
    */
    public function matchcompaniestosharesAction()
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

        // Company name column, defaults to 0
        $companyNameColumn = $request->getParam('company-name-column');
        $outputDir = $request->getParam('output-directory');
        $delimiter = $request->getParam('delimiter');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl($delimiter);

        // Table has header rows
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $j = 0;

        $outputFile = fopen($outputDir . '/' . 'isinMatches.tsv', 'w', chr(9));
        fputcsv($outputFile, array('testName', 'shareName', 'shareISIN', 'current_shareCompany'), chr(9));

        foreach ($fileIterator as $row) {
          $j++;
          $companyName = $row[$companyNameColumn];
          // Simplify LIKE search string
          $companySuffix = $this->getCompanySuffix();
          /* array(' Inc', ' Group', '.', ',', ' Corp', ' Corporation',
          ' group', ' plc', ' PLC', ' Limited', ' limited', ' & Co.',
          ' International', ' Plc', ' S.A.', ' SA', ' Company' );
          */
          $trimmedCompanyName = str_replace($companySuffix, "", $companyName);
          $trimmedCompanyName = trim($trimmedCompanyName);

          $slimCompanyName = str_replace(array(" ", "-", ".", "!"), "", $companyName);
          $slimCompanyName = substr($slimCompanyName, 0, 8);

          //EXCLUDE XS* IN ISIN? BONDS OBLIGATIONS NOT NEEDED

          $result = $entityManager->getRepository('Fund\Entity\Share')
           ->createQueryBuilder('o')
           ->where('o.name LIKE :name')
           ->orWhere('o.name LIKE :slimname')
           ->setParameter('name', '%' . $trimmedCompanyName .'%')
           ->setParameter('slimname', '%' . $slimCompanyName .'%')
           ->getQuery()
           ->getResult();

          foreach ($result as $share) {
             fputcsv($outputFile, array($companyName, $share->name, $share->isin, $share->shareCompany), chr(9));
             $i++;
          }

          // If we had no results here do a search where we remove all
          // whitespaces and shorten the string to a max of 8 char.

        }

        fclose($outputFile);
        echo "Processed $j rows. \n";
        echo "printed $i shares to $outputDir/isinMatches.tsv\n";
    }

    /**
    *
    * This function is used to add listings to the stockexchanges. Matches
    * a ShareCompany to a StockExchange where it also savas the stocks symbol
    * to able to update the stock data later on with the symbol as an id.
    *
    */
    public function addStockListingAction()
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

        // dry-run should make no lasting changes to the database
        $dryRun = $request->getParam('dry-run');
        if ($dryRun == 1) {
          echo "Dry run activiated, no lasting changes will be made. \n";
        }

        // Company name column, defaults to 0
        $symbolColumn = $request->getParam('symbol-column');
        // Company name column, defaults to 1
        $companyNameColumn = $request->getParam('company-name-column');

        $stockExchange = $request->getParam('stock-exchange');

        $delimiter = $request->getParam('delimiter');

        $headerRows = $request->getParam('header-rows');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl($delimiter);

        $fileIterator = new \LimitIterator($file, $headerRows);

        $i = 0;
        $j = 0;
        $h = 0;
        $k = 0;
        $l = 0;

        $se = $entityManager->getRepository('Fund\Entity\StockExchange')
            ->findOneByName($stockExchange);
        if (is_null($se)){
          exit("\' $stockExchange \' stock exchange not found \n  Quitting. \n");
        }

        foreach ($fileIterator as $row) {
            //echo var_dump($row);
            $i++;
            $companyName = $row[$companyNameColumn];
            $stockSymbol = $row[$symbolColumn];


            // Find the company, much match exactly on name
            $company = $entityManager->getRepository('Fund\Entity\ShareCompany')
                ->findOneByName($companyName);

            if (is_null($company)) {
              echo "No company found with the name $companyName.\n";
              $j++;
              continue;
            }

            // Check if the listings already exists
            $result = $entityManager->getRepository('Fund\Entity\StockExchangeListing')
             ->createQueryBuilder('sel')
             ->leftJoin('sel.stockExchange', 'se')
             ->leftJoin('sel.shareCompany', 'sc')
             ->where('se.name LIKE :name')
             ->andWhere('sel.symbol LIKE :symbol')
             ->setParameter('name', $stockExchange)
             ->setParameter('symbol', $stockSymbol)
             ->getQuery()
             ->getResult();

             if (sizeof($result) > 0) {
               // If were in here weve got a match! The stocklisting already exists.
               echo "Listing of $companyName with symbol $stockSymbol on $stockExchange already exists.\n";
               $h++;
              continue;
             }

             // Check if the listing exists with another ticker/symbol
             // Typically for updating tickers!
             $result = $entityManager->getRepository('Fund\Entity\StockExchangeListing')
             ->createQueryBuilder('sel')
             ->leftJoin('sel.stockExchange', 'se')
             ->leftJoin('sel.shareCompany', 'sc')
             ->where('se.name LIKE :stockExchangeName')
             ->andWhere('sc.name LIKE :companyName')
             ->setParameter('stockExchangeName', $stockExchange)
             ->setParameter('companyName', $companyName)
             ->getQuery()
             ->getResult();

             if (sizeof($result) > 0) {

               // If were in here weve got a match! The stocklisting already exists.
               // we should update the listing.



               $seListing = $result[0];
               echo "Updating listing of $companyName from symbol $seListing->stockSymbol to $stockSymbol on $stockExhange. \n";
               //UPDATE LISTING
               $seListing->setSymbol($stockSymbol);

               $k++;
               //$entityManager->persist();
               if ($dryRun == 0) {
                 $entityManager->flush();
               }

               continue;
             }

             // Create stock listing
             $seListing = new StockExchangeListing();
             $seListing->setStockExchange($se);
             $seListing->setSymbol($stockSymbol);
             $seListing->setShareCompany($company);

             $entityManager->persist($seListing);
             $l++;
             if ($dryRun == 0) {
               $entityManager->flush();
             }
          }
        if ($dryRun == 0) {
          $entityManager->flush();
        }
        $entityManager->clear();

        echo "\n ---- Summary ---- \n";
        echo "$i rows handled.\n";
        echo "$l new listings added. \n";
        echo "$j companies not found.\n";
        echo "$h stock listings already added. \n";
        echo "$k stock listings updated with new symbols. \n";


        if ($dryRun == 1) {
          echo "\n ---- Dry run activiated, no changes made. ---- \n\n";
        }
    }



    /**
    *
    * Update market caps from stock exchanges files id on symbol
    * Nasdaq csv for nyse or nasdaq or
    * monthly report from OMXS.
    * Data from the YQL is also possible to insert.
    *
    * To do:
    *
    *
    * Fixed:
    *    - Enable date as a parameter 12 jan 2015
    */
    public function addMarketCapBySymbolAction()
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

        // Symbol column, defaults to 0
        $symbolColumn = $request->getParam('symbol-column');

        // defaults to todays date
        $date = $request->getParam('date');

        $mktCapColumn = $request->getParam('market-cap-column');

        $stockExchange = $request->getParam('stock-exchange');
        $exchangeRate = $request->getParam('exchange-rate');
        if (is_numeric($exchangeRate)) {
          echo "Exchange rate: $exchangeRate. \n";
        } else {
          echo "ex rate is not a numeric $exchangeRate.\n";
        }

        $delimiter = $request->getParam('delimiter');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl($delimiter);

        // Table has header rows
        $fileIterator = new \LimitIterator($file, 1);

        $i = 0;
        $j = 0;
        $h = 0;

        $se = $entityManager->getRepository('Fund\Entity\StockExchange')
            ->findOneByName($stockExchange);
        if (is_null($se)){
          exit("\' $stockExchange \' stock exchange not found \n  Quitting. \n");
        }

        foreach ($fileIterator as $row) {
            //echo var_dump($row);
            $i++;
            $mktCap = $row[$mktCapColumn];
            $stockSymbol = $row[$symbolColumn];


            // Find listing on symbol

            // Check if the listings already exists
            $listing = $entityManager->getRepository('Fund\Entity\StockExchangeListing')
             ->createQueryBuilder('sel')
             ->leftJoin('sel.stockExchange', 'se')
             ->leftJoin('sel.shareCompany', 'sc')
             ->where('se.name LIKE :name')
             ->andWhere('sel.symbol LIKE :symbol')
             ->setParameter('name', $stockExchange)
             ->setParameter('symbol', $stockSymbol)
             ->getQuery()
             ->getOneOrNullResult();

             if (!is_null($listing)) {
               // If were in here weve got a match! The stocklisting exists.
               echo "$stockSymbol matched on $listing->shareCompany market cap: $mktCap \n";
               echo "market cap in sek " .  $mktCap*$exchangeRate . "\n";

               $h++;
               $shareCompany = $listing->shareCompany;

               // clear the market cap formatting, dots, commas whatever
               if (!is_numeric($mktCap)) {
                 echo "mkt cap $mktCap is not a numeric. Could not be handled. \n";
                 continue;
               }

               // convert currency to SEK
               $mktCapSEK = $mktCap*$exchangeRate;

               // update the market cap
               $shareCompany->setMarketValueSEK($mktCapSEK);
               // update date

               $timezone = "Europe/Stockholm";

               // if date isnt set default to todays date.
               if(!$date) {
                 $date = date('m/d/Y');
               }

               $datetimev = \DateTime::createFromFormat(
                   'm/d/Y',
                   $date, // enter datestring here
                   new \DateTimeZone($timezone)
               );

               // set datetime h m s to 0 0 0
               $datetimev->setTime(0, 0, 0);

               $shareCompany->setDate($datetimev);
               $entityManager->persist($shareCompany);
               $entityManager->flush();

            } else {
              // echo "Symbol $stockSymbol not found on se $stockExchange\n";
              $j++;
              continue;
            }


          }

        $entityManager->flush();
        $entityManager->clear();

        echo "$i rows handled.\n";
        echo "$j companies not found.\n";
        echo "$h companies succesfully found on symbol.\n";
    }
    public function addIndustryBySymbolAction() {

      $service = $this->getConsoleService();
      $entityManager = $service->getEM();
      $request = $this->getRequest();

      if (!$request instanceof ConsoleRequest) {
        throw new \RuntimeException('You can only use this action from a console!');
      }

      $symbol = $request->getParam('symbol');
      $industryName = $request->getParam('industry');
      $stockExchange = $request->getParam('stock-exchange');

      // check SE
      $se = $entityManager->getRepository('Fund\Entity\StockExchange')
      ->findOneByName($stockExchange);
      if (is_null($se)){
        exit("\' $stockExchange \' stock exchange not found \n  Quitting. \n");
      }

      // Check symbol
      // Check if the listings already exists
      $result = $entityManager->getRepository('Fund\Entity\StockExchangeListing')
      ->createQueryBuilder('sel')
      ->leftJoin('sel.stockExchange', 'se')
      ->leftJoin('sel.shareCompany', 'sc')
      ->where('se.name LIKE :name')
      ->andWhere('sel.symbol LIKE :symbol')
      ->setParameter('name', $stockExchange)
      ->setParameter('symbol', $symbol)
      ->getQuery()
      ->getResult();

      if (sizeof($result) < 1) {
        //exit ("$stockSymbol on $stockExchange does not exist.\n";)
        // This will be very common, symbol not found.
        exit();
      }
      $company = $result[0]->shareCompany;

      // Check industry
      $industry = $entityManager->getRepository('Fund\Entity\Industry')
      ->findOneByName($industryName);

      if (is_null($industry)){
        exit("Industry: $industryName not found. \n");
      }

      // Update
      $company->setIndustry($industry);

      $entityManager->persist($company);
      $entityManager->flush();
      $entityManager->clear();
      return 1;
      //exit("Successfully set $industryName to " + $company->name + " by symbol $symbol.\n");
    }

    public function updateFundMeasuresAction() {
      $service = $this->getConsoleService();
      $em = $service->getEM();
      $request = $this->getRequest();

      if (!$request instanceof ConsoleRequest) {
        throw new \RuntimeException('You can only use this action from a console!');
      }

      /* FUND MEASURES */
      $fr = $em->getRepository('Fund\Entity\Fund');
      $funds = $fr->findAllFunds();

      // Reset all values.
      foreach ($funds as $fund) {
        $fund->resetMeasures();
        //echo \Doctrine\Common\Util\Debug::dump($fund);
      }

      $funds = $fr->mapControversialMarketValues($funds);

      echo "--- Fund measures --- \n";
      echo "Fund count " . count($funds) . "\n";
      foreach ($funds as $fund) {
        $em->persist($fund);
      }

      /*$em->flush();
      $em->clear();*/

      /* FONDOUT CATEGORY MEASURES */
      echo "--- Fund categories --- \n";
      echo "Fos\tWea\tAlc\tTob\tGam\t"; //1y\t3y\t5y\n";
      echo "- - - - - - - - - - - - - - - - - - - - - - - - - -" .
       " - - - - - - - - - - - - - -\n";
      $funds = new ArrayCollection($funds);
      $fondoutcategoryRepo = $em->getRepository('Fund\Entity\FondoutCategory');
      foreach ($fondoutcategoryRepo->findAll() as $fondoutCategory) {
        $criteria = Criteria::create()->where(
          Criteria::expr()->eq('fondoutcategoryId', $fondoutCategory->id)
        );
        $categoryFunds = $funds->matching($criteria);

        $avgCatFund = $em->getRepository('Fund\Entity\Fund')
            ->findOneByName($fondoutCategory->title);

        if(is_null($avgCatFund)) {
          $avgCatFund = new Fund();
          $avgCatFund->setName($fondoutCategory->title);
          $avgCatFund->setFondoutCategory($fondoutCategory);
          $avgCatFund->setUrl("average" . $fondoutCategory->title);
          $avgCatFund->setActive(0);
        }

        $avgCatFund = $service->findMeasuredAverages($categoryFunds, $avgCatFund);
        $avgCatFund = $service->findNavAverages($categoryFunds, $avgCatFund);

        echo "$avgCatFund->fossilCompaniesPercent\t$avgCatFund->weaponCompaniesPercent\t" .
          "$avgCatFund->alcoholCompaniesPercent\t$avgCatFund->tobaccoCompaniesPercent" .
          "\t$avgCatFund->gamblingCompaniesPercent\t";
        //echo "$avgCatFund->nav1year\t$avgCatFund->nav3year\t$avgCatFund->nav5year\t";
        echo $fondoutCategory->title . "\n";
        $em->persist($avgCatFund);
      }


      echo "--- All funds average --- \n";
      $allFundsAvg = $em->getRepository('Fund\Entity\Fund')
          ->findOneByName('allfunds');

      if(is_null($allFundsAvg)) {
        $allFundsAvg = new Fund();
        $allFundsAvg->setName('allfunds');
        $allFundsAvg->setUrl('allfunds');
        $allFundsAvg->setActive(0);
      }

      $allFundsAvg = $service->findMeasuredAverages($funds, $allFundsAvg);
      $allFundsAvg = $service->findNavAverages($funds, $allFundsAvg);

      echo "$allFundsAvg->fossilCompaniesPercent\t$allFundsAvg->weaponCompaniesPercent\t" .
        "$allFundsAvg->alcoholCompaniesPercent\t$allFundsAvg->tobaccoCompaniesPercent" .
        "\t$allFundsAvg->gamblingCompaniesPercent\t";
      //echo "$allFundsAvg->nav1year\t$allFundsAvg->nav3year\t$allFundsAvg->nav5year   ";
      echo $allFundsAvg->name . "\n";
      $em->persist($allFundsAvg);


      $em->flush();
      $em->clear();

    }

    //Helper functions
    private function getCompanySuffix() {
      return array(' group', '.', ', ', ' corporation', ' group', ' plc', ' limited', ' & co.', ' ab', ' a/s', ' oyj', ' asa', ' hf', ' abp', ' incorporated', ' company', ' & company', ' ag', ' (the)', ' and company', ' holdings', ' financial', 'the ', ' corp', ' inc', ' hldgs', ' companies', ' nl', ' se', 's.p.a.', ' spa', 's.a.', 'aktiengesellschaft');
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


//echo \Doctrine\Common\Util\Debug::dump();
