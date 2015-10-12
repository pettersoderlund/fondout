<?php
namespace Fund;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Fund\Service\FundService;
use Fund\Service\ConsoleService;
use Fund\Service\OrganisationService;
//use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;


class Module implements AutoloaderProviderInterface,
                        //ConsoleBannerProviderInterface,
                        ConsoleUsageProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__),
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(),
            'factories' => array(
                'FundService' => function ($serviceLocator) {
                    $service = new FundService();
                    $service->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));

                    return $service;
                },
                'OrganisationService' => function ($serviceLocator) {
                    $service = new OrganisationService();
                    $service->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));

                    return $service;
                },
                'ConsoleService' => function ($serviceLocator) {
                    $service = new ConsoleService();
                    $service->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));

                    return $service;
                }
            ),
            'invokables' => array(),
            'services' => array(),
            'shared' => array(),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /*
    public function getConsoleBanner(Console $console)
    {
      return 'Fund module';
    }
    */

    public function getConsoleUsage(Console $console)
    {
        return array(
            'update fund-measures'  => 'Updates calculated fund measures of nbr of companies, nav and category stats.',
            'create sitemap'        => 'Prints a sitemap to console. Put in a xml file!',
            'map sharecompanies [--verbose|-v] <csvfileIsinToSharecompany>' => 'Import file to map share isin to share companies.',
            'add companyaccusations [--verbose|-v] <companyAccusations>' => 'Import file to map accusations to share companies.',
            'add sc-marketcap <file>' => 'Import file containing share company market caps in SEK.',
            'add fund --date= [--exchangerate=] [--doubleholdings] [--smallbatch] <file>' => 'Import fund',
            array('--date', 'Date on format m/d/Y'),
            array('--exchangerate', '(optional) Exchange rate for the corresponding date if currency is other than SEK.'),
            array('--smallbatch', 'Use a smaller batch to aviod duplicates in alias.' ),
            array('--doubleholdings', 'If the fund has multiples of the same share use this flag. Make sure the earlier fund entry for this instance is empty.' ),

          );
    }


    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
}
