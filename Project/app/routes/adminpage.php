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

    $users = $sql->allUsersQuery($db_login['database_settings']);
    if ($users == false)
    {
        $log->info('ERROR : Administrator could not retrieve users');
        $users = [];
    }
    else
    {
        $log->info('Administrator retrieved list of users');
    }

    return $this->view->render($response, $users);
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

    //just in case
    if ($params['username'] == 'Administrator')
    {
        $log->info('ERROR : Administrator attempted self ban');
        return $this->response->withRedirect('/manageusers');
    }

    $result = $sql->banUserQuery($db_login['database_settings'], $params['username']);

    if ($result == false)
    {
        $log->info('ERROR : failure to ban user : ' . $params['username']);
        $result = [];
    }

    return $this->view->render($response, $result);
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

   $result = $sql->unbanUserQuery($db_login['database_settings'], $params['username']);
   if ($result == false)
   {
       $log->info('ERROR : failure to unban user : ' . $params['username']);
       $result = [];
   }
   else
   {
       $log->info('Administrator has unbanned user : ' . $params['username']);
   }
   return $this->view->render($response, $result);
});