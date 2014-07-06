<?php
return array(
    'controllers' => array(
            'invokables' => array(
                    'User\Controller\User' => 'Blog\Controller\BlogController'
            ),
    ),
   'view_manager' => array(
        'template_path_stack' => array(
            'user' => __DIR__ . '/../view',
        ),
    ),

    // Start to overwrite zfcuser's route
    'router' => array(
        'routes' => array(
            'zfcuser' => array(
                'options' => array(
                    'route' => '/users',
                ),
            ),
        ),
    )
);
