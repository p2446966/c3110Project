<?php

//define system variables

define('APP_NAME', 'Project'); //this can be changed
define('LANDING_PAGE', $_SERVER['SCRIPT_NAME']);

//Monolog settings

$log_file_path = __DIR__ . '/private_logs/';
$log_file_login_name = 'auth-login.log';
$log_file_login_register = 'auth-register.log';

define('LOGIN_LOG', $log_file_path . $log_file_login_name);
define('REGISTER_LOG', $log_file_path . $log_file_login_register);

//Soap settings

$wsdl = 'https://m2mconnect.ee.co.uk/orange-soap/services/MessageServiceByCountry?wsdl';
define('WSDL', $wsdl);
define('SOAP_USER', '20_2446966');
define('SOAP_PASS', 'Assemena1234');

$error_codes = [
    '404' => 'Error 404 : Page not found',
    '2003' => 'Error 2003 : Login server connection down or refused',
    '1062' => 'Error 1062 : Duplicate entry found',
    '500' => 'Error 500 : Please report to an admin',
];
define('ERROR_CODES', $error_codes);
//define settings container, based off other setting containers
//that all appear the same

$settings = [
    "settings" => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'mode' => 'development',
        'debug' => true,
        'class_path' => __DIR__ . '/src/',
        'view' => [
            'template_path' => __DIR__ . '/templates/',
            'twig' => [
                'cache' => false,
                'auto_reload' => true,
            ],
        ],
        "database_settings" => [
           'HOST' => 'localhost',
           'USER' => 'apiprojectmanager',
           'PASS' => '0t4KO0eyKTIijJro',
           'NAME' => 'apiproject',
        ],
    ],
];

return $settings;
