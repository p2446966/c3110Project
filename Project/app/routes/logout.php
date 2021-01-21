<?php
/*
 * Doesn't Require SQL.
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * Logout request to log user out of session
 */
$app->get('/logout', function (Request $request, Response $response) use ($app) {
    session_start();
    //Unset all of the session variables.
    $_SESSION = array();

    /**
     * If it's desired to kill the session, also delete the session cookie.
     * This will destroy the session, and not just the session data
     */
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();

    return $this->response->withRedirect('/');
})->setName('Logout');