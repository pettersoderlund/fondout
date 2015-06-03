<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Fund\Controller\Fund' => 'Fund\Controller\FundController',
            'Fund\Controller\Console' => 'Fund\Controller\ConsoleController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'funds' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/funds[/:id]',
                    'constraints' => array(
                        'id' => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund'
                    ),
                ),
            ),
            'fundcompany' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/fundcompany/:name',
                    'constraints' => array(
                        'name' => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund',
                        'action'     => 'getFundCompany'
                    ),
                ),
            ),
            'qa' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/qa',
                    'constraints' => array(),
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund',
                        'action'     => 'getQA'
                    ),
                ),
            ),
            'press' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/pressrelease',
                    'constraints' => array(),
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund',
                        'action'     => 'getPress'
                    ),
                ),
            ),
            'change-sustainability-categories' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/funds/change-sustainability-categories',
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund',
                        'action'     => 'changeCategories',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
          'sustainability-categories'              => __DIR__ . '/../view/fund/fund/sustainability-categories.phtml',
          'sustainability-categories-explained'    => __DIR__ . '/../view/fund/fund/sustainability-categories-explained.phtml',
          'example-companies'                      => __DIR__ . '/../view/fund/fund/example-companies.phtml',
          'alternate-funds'                        => __DIR__ . '/../view/fund/fund/alternate-funds.phtml',
          'how-to-search'                          => __DIR__ . '/../view/fund/fund/how-to-search.phtml',
        ),

        'template_path_stack' => array(
            'Fund' => __DIR__ . '/../view',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'my_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Fund/Entity')
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'Fund\Entity' => 'my_annotation_driver'
                )
            )
        )
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
              'update-fund-measures' => array(
                  'options' => array(
                      'route'    =>
                       'update fund-measures',
                      'defaults' => array(
                          'controller' => 'Fund\Controller\Console',
                          'action'     => 'updateFundMeasures'
                      )
                  )
              ),
                'map-share-companies' => array(
                    'options' => array(
                        'route'    =>
                         'map sharecompanies [--verbose|-v] <csvfileIsinToSharecompany>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'mapsharecompanies'
                        )
                    )
                ),
                'add-company-accusations' => array(
                    'options' => array(
                        'route'    =>
                            'add companyaccusations [--verbose|-v] <companyAccusations>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addcompanyaccusations'
                        )
                    )
                ),
                'add-source' => array(
                    'options' => array(
                        'route'    =>
                            'add sources [--verbose|-v] <sources>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addsource'
                        )
                    )
                ),
                'add-carbon-tracker' => array(
                    'options' => array(
                        'route'    =>
                            'add carbontracker [--verbose|-v] <file>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addcarbontracker'
                        )
                    )
                ),
                'add-fund' => array(
                    'options' => array(
                        'route'    =>
                            'add fund --date= [--exchangerate=] [--doubleholdings] [--smallbatch] <file>',
                        'defaults' => array(
                            'controller'     => 'Fund\Controller\Console',
                            'action'         => 'addfund',
                            'doubleholdings' => false,
                            'smallbatch'     => false
                        )
                    )
                ),
                'add-bank-listing' => array(
                    'options' => array(
                        'route'    =>
                            'add banklisting <file> <bank>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addbanklisting'
                        )
                    )
                ),
                'add-share-company-market-cap' => array(
                    'options' => array(
                        'route'    =>
                            'add sc-marketcap <file>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addmarketcap'
                        )
                    )
                ),
                'add-emissions' => array(
                    'options' => array(
                        'route'    =>
                            'add emissions <file>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addemissions'
                        )
                    )
                ),
                'match-companies' => array(
                    'options' => array(
                        'route'    =>
                            'match companies <file> [--company-name-column=] [--output-directory=] [--market-cap|-m] [--delimiter=]',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'matchcompanies',
                            'company-name-column' => '0',
                            'output-directory' => '.',
                            'delimiter' => chr(9)
                        )
                    )
                ),
                'match-companies-on-shares' => array(
                    'options' => array(
                        'route'    =>
                            'match companiestoshares <file> [--company-name-column=] [--output-directory=] [--delimiter=]',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'matchcompaniestoshares',
                            'company-name-column' => '0',
                            'output-directory' => '.',
                            'delimiter' => chr(9)
                        )
                    )
                ),
                'add-stock-exchange-listing' => array(
                    'options' => array(
                        'route'    =>
                            'add stock-exchange-listing <file> --stock-exchange= [--company-name-column=] [--symbol-column=]  [--delimiter=] [--dry-run] [--header-rows=]',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addStockListing',
                            'company-name-column' => '1',
                            'symbol-column' => '0',
                            'dry-run' => '0',
                            'delimiter' => chr(9),
                            'header-rows' => '0'
                        )
                    )
                ),
                'add-market-cap-from-symbol' => array(
                    'options' => array(
                        'route'    =>
                            'add market-cap-by-symbol <file> --stock-exchange= --exchange-rate= [--market-cap-column=] [--symbol-column=]  [--delimiter=] [--date=]',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addMarketCapBySymbol',
                            'market-cap-column' => '3',
                            'symbol-column' => '0',
                            'delimiter' => chr(9)
                        )
                    )
                ),
                'add-industry-by-yahoo-symbol' => array(
                  'options' => array(
                    'route'    =>
                    'add industry-by-symbol --symbol= --industry= [--stock-exchange=] ',
                    'defaults' => array(
                      'controller' => 'Fund\Controller\Console',
                      'action'     => 'addIndustryBySymbol',
                      'stock-exchange' => 'yahoo'
                    )
                  )
                ),
            ),
        ),
    ),
);
