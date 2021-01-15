<?php

//refresh button to view users
//ban and unban buttons for users

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->get('/manageusers', function (Request $request, Response $response) use ($app){
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    if (!isset($_SESSION['Logged_in']) || !$_SESSION['Logged_in'] || !isset($_SESSION['USERNAME'])) // If not logged in (including malformed ways) redirect to login page.
    {
      return $this->response->withRedirect('/login');
    } elseif ($_SESSION['USERNAME'] != 'Administrator') { // If not administrator redirect to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

    return $this->view->render($response, 'adminmanageusers.html.twig', $twigsArray);
});

$app->post('/refresh-users', function (Request $request, Response $response) use ($app){
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(ADMIN_LOG, Logger::INFO));

    if ($_SESSION['USERNAME'] != 'Administrator') { // If not administrator redirect to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

    $sql = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');

    $users = $sql->allUsersQuery($db_login['database_settings']);                                           // NOT FINISHED SO DO NOT USE YET.
    if ($users == false)
    {
        $log->info('ERROR : Administrator could not retrieve any users');
        $users = [];
    }
    else
    {
        $log->info('Administrator retrieved list of users');
    }

    //return generation
    $return = new SimpleXMLElement('<xml/>');
    $users_results = $return->addChild('users_results');

    if ($users >= 1)
    {
        $users_results->addChild('success', "true");
        $users_results->addChild('message', "");
    }
    else {
        $users_results->addChild('success', "false");
        $users_results->addChild('message', "");
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});

$app->post('/ban-user', function (Request $request, Response $response) use ($app){
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(ADMIN_LOG, Logger::INFO));

    if ($_SESSION['USERNAME'] != 'Administrator') { // If not administrator redirect to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

    $params = $request->getParsedBody();

    $sql = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');

    //just in case... lol
    if ($params['username'] == 'Administrator')
    {
        $log->info('ERROR : Administrator attempted self ban');
        $result = false;
    } else {
        $result = $sql->banUserQuery($db_login['database_settings'], $params['username']);
    }

    if ($result == false)
    {
        $message = 'ERROR: failure to ban user : ' . $params['username'];
        $log->info($message);
    } else {
        $message = 'The user' . $params['username'] . 'has been banned';
    }

    //return generation
    $return = new SimpleXMLElement('<xml/>');
    $ban_results = $return->addChild('ban_results');

    if ($result === true)
    {
        $ban_results->addChild('success', "true");
        $ban_results->addChild('message', $message);
    }
    else {
        $ban_results->addChild('success', "false");
        $ban_results->addChild('message', $message);
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});

$app->post('/unban-user', function (Request $request, Response $response) use ($app){
   session_start();

   $log = new Logger('logger');
   $log->pushHandler(new StreamHandler(ADMIN_LOG, Logger::INFO));

    if ($_SESSION['USERNAME'] != 'Administrator') { // If not administrator redirect to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

   $params = $request->getParsedBody();

   $sql = $app->getContainer()->get('SQLQueries');
   $db_login = $app->getContainer()->get('settings');


    if ($params['username'] == 'Administrator') {
        $log->info('ERROR : Administrator attempted self unban. Prevent so password is preserved.');
        $result = false;
    } else {
        $result = $sql->unbanUserQuery($db_login['database_settings'], $params['username']);
    }

   if ($result === true)
   {
       $message = 'Unbanned user "' . $params['username'] .'". Password is now default.';
       $log->info($message);
   }
   else
   {
       $message = 'ERROR: failure to unban user "' . $params['username'] . '".';
       $log->info($message);
   }

    //return generation
    $return = new SimpleXMLElement('<xml/>');
    $unban_results = $return->addChild('unban_results');

    if ($result == true)
    {
        $unban_results->addChild('success', "true");
        $unban_results->addChild('message', $message);
    }
    else {
        $unban_results->addChild('success', "false");
        $unban_results->addChild('message', $message);
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});