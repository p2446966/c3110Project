<?php
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * Homepage should appear upon request and login.
 * Successful log in or register will redirect to home page.
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response) use ($app)
{
    session_start();

    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    return $this->view->render($response,
        'homepage.html.twig', $twigsArray);
});