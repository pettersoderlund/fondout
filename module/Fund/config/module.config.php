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
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fund\Controller\Fund'
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
                            'add fund --date= [--exchangerate=] <file>',
                        'defaults' => array(
                            'controller' => 'Fund\Controller\Console',
                            'action'     => 'addfund'
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
            ),
        ),
    ),
);
