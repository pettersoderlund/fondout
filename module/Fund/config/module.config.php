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
            ),
        ),
    ),
);
