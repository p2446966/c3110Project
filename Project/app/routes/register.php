<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/// Register ///
$app->get('/register', function (Request $request, Response $response) use ($app) {
    session_start();

    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();
    return $this->view->render($response, 'register.html.twig', $twigsArray);
})->setName('Register');

/// auth-register ///
$app->post('/auth-register', function (Request $request, Response $response) use ($app) {
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(REGISTER_LOG, Logger::INFO));

    $params = $request->getParsedBody();

    $validator = $app->getContainer()->get('validator');
    $cleaned_params = $validator->validateInput($params);

    $database = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');
    $register_success = $database->registerQuery($db_login['database_settings'], $cleaned_params[0], $cleaned_params[1], $params['phone']);

    if ($register_success === true)
    {
        $_SESSION['Logged_in'] = true;
        $log->info('Register success: ' . $cleaned_params[0]);
        $register_success = "Register Success. Logged in.";
    }
    else
    {
        $_SESSION['Logged_in'] = false;
        $log->info('Register Failure: ' . $cleaned_params[0]);
    }
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    $twigsArray['register_success'] = $register_success;

    return $this->view->render($response, 'register_results.html.twig', $twigsArray, $register_success);
})->setName('Authorising Registration');
