<?php
use src\SQLQueries as SQLQ;
require 'Project/app/src/SQLQueries.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/// Login ///
$app->get('/login', function(Request $request, Response $response) {
    session_start();

    /* End */

    return $this->view->render(
        $response,
        'login.html.twig',
        [
            // Login navigation
            'username' => $_SESSION['UID'],
        ]
    );
})->setName('Login');

$app->post('/auth-login', function (Request $request, Response $response) {
    session_start();

    /* Error Logging */
    $logs_file_path = __DIR__ . '/private_logs/';
    $logs_file_name = 'auth-login.log';
    $logs_file = $logs_file_path . $logs_file_name;

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler($logs_file, Logger::INFO));
    /* End*/

    $params = $request->getParsedBody();

    $username = $params['username'];
    $password = $params['password'];

    //Basic check to remove unwanted characters. If invalid characters are present the query is never made for security reasons otherwise the query is made and a result is given.
    if (preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $username))
    {
        $login_success = "Your username contains special characters not allowed ( ()}{@#/<>,[]\,'|\" )";
        $log->warning("User posted invalid chars despite form validation. Username: " . $username);
    } else {
        $login_success = SQLQ::loginQuery($log, $username, $password);
    }

    // Defining what to tell the user. Success or failure of login.
    if ($login_success == "true")
    {
        $_SESSION['logged_in'] = true;
        $log->info('Login Success: ' . $username);
    } else {
        $_SESSION['logged_in'] = false;
        $log->info('Login Attempt: ' . $username);
    }

    return $this->view->render(
        $response,
        'login_results.html.twig',
        [
            // Login navigation
            'username' => $username,

            // Page vars
            'login_success' => $login_success,
        ]
    );
})->setName('Authorising Login');




/// Register ///
$app->get('/register', function(Request $request, Response $response) {
    session_start();

    /* End */
    return $this->view->render(
        $response,
        'register.html.twig',
        [
            // Not data yet. Placeholder.
        ]
    );

})->setName('Register');

$app->post('/auth-register',
    function (Request $request, Response $response) {
        session_start();

        /* Error Logging */
        $logs_file_path = __DIR__ . '/private_logs/';
        $logs_file_name = 'auth-register.log';
        $logs_file = $logs_file_path . $logs_file_name;

        $log = new Logger('logger');
        $log->pushHandler(new StreamHandler($logs_file, Logger::INFO));
        /* End*/

        /* Gathering post data. */
        $params = $request->getParsedBody();

        $username = $params['username'];
        $password = $params['password'];
        $passwordConfirm = $params['password-confirm'];
        $email = $params['email'];

        /* PHP Form validation */
        if (preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $username))
        {
            $register_success = "Your username contains special characters not allowed ( ()}{#/<>,[]\,'|\" )";
            $log->warning("User posted invalid chars despite form validation. Username: " . $username);
        } else if ($password != $passwordConfirm) {
            $register_success = "Passwords do not match.";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $email)) {
            $register_success = "Invalid email format / invalid characters.";
            $log->warning("User posted invalid chars or email format despite form validation. Email: " . $email);
        } else {
            /* End */

            /* Password Security */
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Uses strongest at time till php updates then it will use a newer. Older hashes i believe are compatible with password_verify however.
            password_verify($password, $hashedPassword); // Can be removed but is here to remind you that this is needed to verify passwords with the above function.
            /* end */

            $register_success = SQLQ::registerQuery($log, $username, $hashedPassword, $email);

            if ($register_success) {
                $_SESSION['logged_in'] = true;
                $log->notice('Register Success: ' . $username);
            } else {
                $_SESSION['logged_in'] = false;
            }
        }
        return $this->view->render(
            $response,
            'register_results.html.twig',
            [
                // Login navigation
                'username' => $username,

                // Page vars
                'register_success' => $register_success,
            ]);
    })->setName('Authorising Registration');




/// Other/Logout ///
$app->get('/logout', function (Request $request, Response $response) use ($app) {
    session_start();
    require_once "lib/logout.php"; //Do not give this app Session

    return $this->response->withRedirect('/');
})->setName('Logout');
