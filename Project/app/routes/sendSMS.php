<?php

//retrieve params
//validate
//send message

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->get('/Send', function(Request $request, Response $response) use ($app){
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();
    if (!isset($_SESSION['Logged_in']) && !$_SESSION['Logged_in'])
    {
        return $this->response->withRedirect('/login');
    }
    return $this->view->render($response, 'sendmessagepage.html.twig', $twigsArray);
});

$app->post('/auth-send', function (Request $request, Response $response) use ($app){

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(MESSAGE_LOG, Logger::INFO));

    $validator = $app->getContainer()->get('validator');

    $params = $request->getParsedBody();
    $cleaned_dest = $validator->sanitiseString($params['destination']);
    $cleaned_message = $validator->sanitiseString($params['message']);

    $soap = $app->getContainer()->get('soapWrapper');
    $soap_handle = $soap->createSoapClient();
    //returns true if message was sent successfully
    $result = $soap->sendSMS($soap_handle, SOAP_USER, SOAP_PASS, $cleaned_dest, $cleaned_message);

    if ($result)
    {
        $process_outcome = "Message Sent Successfully";
        $log->info('User : ' . $_SESSION['USERNAME'] . ' sent message to SMS : ' . $cleaned_dest);
    }
    else
    {
        $process_outcome = "Error: Message could not be sent";
        $log->info('ERROR : User : ' . $_SESSION['USERNAME'] . ' recieved error sending to SMS : ' . $cleaned_dest);
    }

    return $this->view->render($response, $process_outcome);
});


