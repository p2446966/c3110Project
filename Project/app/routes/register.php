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
    $register_params['username'] = $params['username'];
    $register_params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);

    $validator = $app->getContainer()->get('validator');
    $cleaned_params = $validator->validateInput($register_params);
    $cleaned_phone_param = $validator->validatePhoneNumber($params['phone']);
    
    $database = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');
    $register_success = $database->registerQuery($db_login['database_settings'], $cleaned_params[0], $cleaned_params[1], $cleaned_phone_param);

    //return generation
    $return = new SimpleXMLElement('<xml/>');
    $register_results = $return->addChild('register_results');

    if ($register_success === true)
    {
        $_SESSION['Logged_in'] = true;
        $log->info('Register success: ' . $cleaned_params[0]);
        $register_success = "Register Success. Logged in.";

        $register_results->addChild('success', "true");
        $register_results->addChild('message', "User details registered successfully and logged in.");
    }
    else
    {
        $_SESSION['Logged_in'] = false;
        $log->info('Register Failure: ' . $cleaned_params[0]);

        $register_results->addChild('success', "false");
        $register_results->addChild('message', $register_success);
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
})->setName('Authorising Registration');
