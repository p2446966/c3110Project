<?php

//require 'Project/app/src/SQLQueries.php';
use src\SQLQueries;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/// Login ///
$app->get('/login', function(Request $request, Response $response) {
    session_start();

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {               //TODO Make a class that returns this so it can be called dynamically elsewhere
        $twigsArray = [
            'username' => $_SESSION['USERNAME'],
            'loginFirstLink' =>  "#",
            'loginFirstText' => "Placeholder",
            'loginSecondLink' =>  "/logout",
            'loginSecondText' => "Logout",
        ];
    } else {
        $twigsArray = [
            'username' => "Accounts",
            'loginFirstLink' =>  "/login",
            'loginFirstText' => "Login",
            'loginSecondLink' =>  "/register",
            'loginSecondText' => "Register",
        ];
    }

    return $this->view->render( $response,'login.html.twig', $twigsArray);
})->setName('Login');

$app->post('/auth-login', function (Request $request, Response $response) {
    session_start();

    /* Error Logging */                                 //TODO Convert to an object or function with a parameter to set the log file name and then give it a correct path.
    $logs_file_path = __DIR__ . '/private_logs/';
    $logs_file_name = 'auth-login.log';
    $logs_file = $logs_file_path . $logs_file_name;

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler($logs_file, Logger::INFO));
    /* End*/

    $params = $request->getParsedBody();

    //Basic check to remove unwanted characters. If invalid characters are present the query is never made for security reasons otherwise the query is made and a result is given.
    if (preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $params['username']))
    {
        $login_success = "Your username contains special characters not allowed ( ()}{@#/<>,[]\,'|\" )";
        $log->warning("User posted invalid chars despite form validation. Username: " . $params['username']);
    } else {
        $login_success = SQLQueries::loginQuery($log, $params['username'], $params['password']);
    }

    // Defining what to tell the user. Success or failure of login.
    if ($login_success == "true")
    {
        $_SESSION['logged_in'] = true;
        $log->info('Login Success: ' . $params['username']);
    } else {
        $_SESSION['logged_in'] = false;
        $log->info('Login Attempt: ' . $params['username']);
    }

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {               //TODO Make a class that returns this so it can be called dynamically elsewhere
        $twigsArray = [
            'username' => $_SESSION['USERNAME'],
            'loginFirstLink' =>  "#",
            'loginFirstText' => "Placeholder",
            'loginSecondLink' =>  "/logout",
            'loginSecondText' => "Logout",
        ];
    } else {
        $twigsArray = [
            'username' => "Accounts",
            'loginFirstLink' =>  "/login",
            'loginFirstText' => "Login",
            'loginSecondLink' =>  "/register",
            'loginSecondText' => "Register",
        ];
    }

    return $this->view->render($response, 'login_results.html.twig', $twigsArray, $login_success);
})->setName('Authorising Login');