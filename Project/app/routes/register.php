<?php

//require 'Project/app/src/SQLQueries.php';
use src\SQLQueries;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/// Register ///
$app->get('/register', function(Request $request, Response $response) {
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

    /* End */
    return $this->view->render($response,'register.html.twig', $twigsArray);

})->setName('Register');

$app->post('/auth-register',
    function (Request $request, Response $response) {
        session_start();

        /* Error Logging Setup*/                            //TODO Convert to an object or function with a parameter to set the log file name and then give it a correct path.
        $logs_file_path = __DIR__ . '/private_logs/';
        $logs_file_name = 'auth-register.log';
        $logs_file = $logs_file_path . $logs_file_name;

        $log = new Logger('logger');
        $log->pushHandler(new StreamHandler($logs_file, Logger::INFO));
        /* End*/

        /* Gathering post data. */
        $params = $request->getParsedBody();

        /* PHP Form validation */
        if (preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $params['username']))
        {
            $register_success = "Your username contains special characters not allowed ( ()}{#/<>,[]\,'|\" )";
            $log->warning("User posted invalid chars despite form validation. Username: " . $params['username']);
        } else if ($params['password'] != $params['password-confirm']) {
            $register_success = "Passwords do not match.";
        } else if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL) || preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $params['email'])) {
            $register_success = "Invalid email format / invalid characters.";
            $log->warning("User posted invalid chars or email format despite form validation. Email: " . $params['email']);
        } else { /* End Validation*/

            /* Password Security */
            $hashedPassword = password_hash($params['password'], PASSWORD_DEFAULT); // Uses strongest at time till php updates then it will use a newer method. Older hashes i believe are compatible with password_verify however.
            password_verify($params['password'], $hashedPassword); // Can be removed but is here to remind you that this is needed to verify passwords when hashing with the above function.
            /* end */

            $register_success = SQLQueries::registerQuery($log, $params['username'], $hashedPassword, $params['email']);

            if ($register_success) {
                $_SESSION['logged_in'] = true;
                $log->notice('Register Success: ' . $params['username']);
            } else {
                $_SESSION['logged_in'] = false;
            }
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

        return $this->view->render($response, 'register_results.html.twig', $twigsArray, $register_success);
    })->setName('Authorising Registration');