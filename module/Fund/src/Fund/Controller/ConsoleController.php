<?php
namespace Fund\Controller;

use SplFileObject;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Fund\Entity\ShareCompany;
use Fund\Entity\Source;

class ConsoleController extends AbstractActionController
{
    protected $consoleService;
    protected $em;

    public function mapsharecompaniesAction ()
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
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Open file passed through argument
        // Open CSV file
        $file = new SplFileObject(
            $this->getRequest()->getParam('companyAccusations')
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
            $shareCompanyName = $row[0];
            $accusation = $row[1];
            $category = $row[2];
            $source = $row[3];

            // Check source
            // Check sharecompany
            // Check category - create if not already existing
            // Create accusation
            // Fin
        }
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
        $file->setCsvControl(",");
        // Skip header row
        $fileIterator = new \LimitIterator($file, 1);

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

            $source = $em->getRepository('Fund\Entity\Source')
                ->findOneBy(['name' => $name]);

            // Create or update source
            // Create source if it does not already exist
            if (is_null($source)) {
                // Create the sharecompany missing
                $source = new Source();

            } elseif ($source->releaseDate != $datetimev) {
                // If the date differs, create new source entry
                $source = new Source();
            }

            $source->setName($name);
            $source->setUrl($url);
            $source->setReleaseDate($datetimev);

            $em->persist($source);
            $em->flush();
            $em->clear();
        }
    }

    public function getConsoleService()
    {
        if (!$this->consoleService) {
            $this->consoleService = $this->getServiceLocator()->get('ConsoleService');
        }

        return $this->consoleService;
    }
}
