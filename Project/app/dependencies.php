<?php

require 'vendor\autoload.php';

require 'Project/app/src/Validator.php';
require 'Project/app/src/SoapWrapper.php';
require 'Project/app/src/XMLParser.php';
require 'Project/app/src/DatabaseWrapper.php';
require 'Project/app/src/SQLQueries.php';
require 'Project/app/src/SessionsModel.php';

//pre-fab code to register twig templates into slim


/**
 * @param $container
 * @return \Slim\Views\Twig
 */
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'],
        $container['settings']['view']['twig'],
        [
            'debug' => true
        ]
    );

    // Instantiate and add Slum specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

//register components from src into the container
$container['validator'] = function ($container) {
    $validator = new \Telemetry\Validator();
    return $validator;
};

/**
 * @param $container
 * @return \Telemetry\SoapWrapper
 */
$container['soapWrapper'] = function ($container) {
    $soapWrapper = new \Telemetry\SoapWrapper();
    return $soapWrapper;
};

$container['xmlParser'] = function ($container) {
    $xmlParser = new \Telemetry\XMLParser();
    return $xmlParser;
};

/**
 * @param $container
 * @return \Telemetry\DatabaseWrapper
 */
$container['databaseWrapper'] = function ($container) {
    $databaseWrapper = new \Telemetry\DatabaseWrapper();
    return $databaseWrapper;
};

$container['SQLQueries'] = function ($container) {
    $SQLQueries = new \Telemetry\SQLQueries();
    return $SQLQueries;
};

/**
 * @param $container
 * @return \Telemetry\SessionsModel
 */
$container['sessionsModel'] = function ($container) {
    $sessionsModel = new \Telemetry\SessionsModel();
    return $sessionsModel;
};
