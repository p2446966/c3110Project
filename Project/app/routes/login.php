<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/// login ///
$app->get('/Login', function(Request $request, Response $response) use ($app) {
    session_start();
    $twigsArray = getPlaceholders($app->getContainer()->get('sessionsModel')->getStatus());
    return $this->view->render($response, 'login.html.twig', $twigsArray);
})->setName('Login');

/// auth-login ///
$app->post('/auth-login', function (Request $request, Response $response) use ($app) {
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(LOGIN_LOG, Logger::INFO));

    $params = $request->getParsedBody();

    $validator = $app->getContainer()->get('validator');
    $login_params = $validator->validateInput($params);

    $database = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');
    $login_success = $database->loginQuery($db_login['database_settings'], $login_params['username'], $login_params['password']);

    if ($login_success == true)
    {
        $_SESSION['Logged_in'] = true;
        $log->info('Login Success: ' . $login_params['username']);
    }
    else
    {
        $_SESSION['Logged_in'] = false;
        $log->info('Login Attempt: ' . $login_params['username']);
    }

    $twigsArray = getPlaceholders($app->getContainer()->get('sessionsModel')->getStatus());

    return $this->view->render($response, 'login_results.html.twig', $twigsArray, $login_success);

})->setName('Authorising Login');
