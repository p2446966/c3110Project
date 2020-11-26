<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
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

    return $this->view->render($response,
        'homepage.html.twig', $twigsArray);
});