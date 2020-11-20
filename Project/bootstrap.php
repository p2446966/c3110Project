<?php

require 'vendor/autoload.php';

//create settings

$settings = require __DIR__ . '/app/settings.php';
$container = new \Slim\Container($settings);

//create dependencies

require __DIR__ . '/app/dependencies.php';
$app = new \Slim\App($container);

//create routes

require __DIR__ . '/app/routes.php';

$app->run();