<?php
/**
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed $errormessage
 * error message dependant on request type and redirection to error page.
 * error messages are for when page is redirected for request that is not possible based on request.
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get( '/error/{number}', function(Request $request, Response $response, $args) use ($app)
{
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();
    $errormessage = "";
    switch ($args['number'])
    {
        case (400):
        {
            $errormessage .= "The server couldn't understand the request.";
            break;
        }
        case (401):
        {
            $errormessage .= "Could not authorize the request. Most likely no means of authorization possible.";
            break;
        }
        case (403):
        {
            $errormessage .= "Unauthorized access request made.";
            break;
        }
        case (404):
        {
            $errormessage .= "Request or resource could not be found... Sorry :(";
            break;
        }
        case (405):
        {
            $errormessage .= "API or method not allowed.";
            break;
        }
        case (406):
        {
            $errormessage .= "Unconformable request sent.";
            break;
        }
        case 501:
        {
            $errormessage .= "Not implemented.";
            break;
        }
        default: //Basically Error 500.
        {
            $errormessage .= "Generic server error that the server couldn't understand or handle.";
            break;
        }
    }

    $twigsArray['error_number'] = $args['number'];
    $twigsArray['error_message'] = $errormessage;
    return $this->view->render($response,'error.html.twig', $twigsArray);
});