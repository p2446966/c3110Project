<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
    return $this->view->render($response,
    'testhomepage.html.twig',
    [
       'test_data' => 'this is a test'
    ]);
});