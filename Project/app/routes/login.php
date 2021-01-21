<?php
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * Login code for login page request
 * Open login page for users to log in.
 */
$app->get('/login', function(Request $request, Response $response) use ($app) {
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();
    return $this->view->render($response, 'login.html.twig', $twigsArray);
});

/**
 * @param Request $request
 * @param Response $response
 * Auth-login code to authorise user and log user info to admin pannel and database.
 * Successful log in attempt will return 'successful login' and redirection of page.
 * unsuccessful login return 'Incorrect user name and password' message and redirect to login page.
 */
$app->post('/auth-login', function (Request $request, Response $response) use ($app) {
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(LOGIN_LOG, Logger::INFO));

    $params = $request->getParsedBody();

    $validator = $app->getContainer()->get('validator');
    $login_params = $validator->validateInput($params);

    $database = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');

    $login_success = $database->loginQuery($db_login['database_settings'], $login_params[0], $login_params[1]);

    // return generation
    $return = new SimpleXMLElement('<xml/>');
    $login_results = $return->addChild('login_results');

    if ($login_success == true)
    {
        $_SESSION['Logged_in'] = true;
        $log->info('Login Success: ' . $login_params[0]);

        $login_results->addChild('success', "true");
        $login_results->addChild('message', "User successfully logged in.");
    }
    else {
        $_SESSION['Logged_in'] = false;
        $log->info('Login Attempt: ' . $login_params[0]);

        $login_results->addChild('success', "false");
        $login_results->addChild('message', "Incorrect username or password.");
    }

    // return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});
