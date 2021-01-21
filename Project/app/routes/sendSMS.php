<?php


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * this is to request a message to be sent to a UK '+44' number.
 *
 * end messages
 */
$app->get('/send', function(Request $request, Response $response) use ($app){
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();
    if (!isset($_SESSION['Logged_in']) && !$_SESSION['Logged_in'])
    {
        return $this->response->withRedirect('/login#send');
    }
    return $this->view->render($response, 'sendmessagepage.html.twig', $twigsArray);
});
/**
 * @param Request $request
 * @param Response $response
 * request is validated to ensure user is authorised
 * message sent when all field filled in correct format
 * returns true if message was sent successfully
 * with message to user 'Message Sent Successfully'
 * messages that do not send a 'Error: Message could not be sent', usually format issue.
 */
$app->post( '/auth-send', function (Request $request, Response $response) use ($app){
    session_start();

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

    //return generation
    $return = new SimpleXMLElement('<xml/>');

    if ($result === true)
    {
        $return->addChild('success', "true");
        $return->addChild('message', $process_outcome);
    }
    else {
        $return->addChild('success', "false");
        $return->addChild('message', $process_outcome);
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});


