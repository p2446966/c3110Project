<?php

//in templates the soap twig file uses {% for row in downloadsresults %}
//so i'm assuming that also needs to be parsed to the twig render

//first soap gets checked for new messages
//anything new gets retrieved, parsed, validated and added to the database
//database then retrieves all of the user's messages in the form of an array of arrays

//change and move things around if you need to

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/soap', function(Request $request, Response $response) use ($app)
{
    session_start();

    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    if (!isset($_SESSION['Logged_in']) && !$_SESSION['Logged_in'])
    {
        return $this->response->withRedirect('/login#soap');
    }

    //retrieve messages
    $soap = $app->getContainer()->get('soapWrapper');
    $soap_handle = $soap->createSoapClient();
    $recSMS = $soap->recieveSMS($soap_handle, SOAP_USER, SOAP_PASS, 10, $_SESSION['phone']);
    
    //parse XML
    $xml = $app->getContainer()->get('xmlParser');
    $xml->setXMLData($recSMS);
    $xml->parseXML(); //now in array form
    $messages = $xml->getData();
    
    //validate and store
    $sql = $app->getContainer()->get('SQLQueries');
    $validator = $app->getContainer()->get('validator');
    $db_login = $app->getContainer()->get('settings');
    
    //retrieve stored messages
    $stored_messages = $sql->retrieveTelemetry($db_login['database_settings']);
    
    //fetch latest timestamp
    if (sizeof($stored_messages) > 0)
    {
        $latest_timestamp = $stored_messages[sizeof($stored_messages) - 1]['receivedtime'];
        $last_timestamp = str_replace([' ', '/', ':'], '', $latest_timestamp);
    }
    else
    {
        $last_timestamp = 0;
    }
        
    foreach ($messages as $message)
    {
        $clean_message = $validator->validateTelemetry($message);
        if ($clean_message != false)
        {
            //check timestamp against current
            $current_time = str_replace([' ', '/', ':'], '', $clean_message['receivedtime']);
            if ($current_time > $last_timestamp)
            {
                //send to database
                $sql->storeTelemetry($db_login['database_settings'], $message);
                //add to current message list
                array_push($stored_messages, $clean_message);
            }
        }
    }
    
    return $this->view->render($response,
        'soap.html.twig',
        [
            'username' => $twigsArray['username'],
            'loginFirstLink' => $twigsArray['loginFirstLink'],
            'loginFirstText' => $twigsArray['loginFirstText'],
            'loginSecondLink' => $twigsArray['loginSecondLink'],
            'loginSecondText' => $twigsArray['loginSecondText'],
            'downloadsresults' => $stored_messages
        ]);
});
