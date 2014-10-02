<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'fondout',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Tree\TreeListener',
                ),
            ),
        ),
        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'charset' => 'utf8',
                    'driverOptions' => array(
                        1002=>'SET NAMES utf8'
                    ),
                    'host'     => getenv('OPENSHIFT_MYSQL_DB_HOST'),
                    'port'     => getenv('OPENSHIFT_MYSQL_DB_PORT'),
                    'user'     => getenv('OPENSHIFT_MYSQL_DB_USERNAME'),
                    'password' => getenv('OPENSHIFT_MYSQL_DB_PASSWORD'),
                    'dbname'   => getenv('OPENSHIFT_GEAR_NAME')
                )
            )
        )
    )
);
