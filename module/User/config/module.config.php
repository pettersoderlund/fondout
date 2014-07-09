<?php
namespace User;

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
    ),

    'doctrine' => array(
      'driver' => array(
        __NAMESPACE__ . '_driver' => array(
          'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
          'cache' => 'array',
          'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
        ),
        'orm_default' => array(
          'drivers' => array(
          __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
        )
      )
    )
  )
);
