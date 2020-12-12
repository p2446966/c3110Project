<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/soap', function(Request $request, Response $response) use ($app)
{
    session_start();

    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    return $this->view->render($response,
        'soap.html.twig', $twigsArray);
});