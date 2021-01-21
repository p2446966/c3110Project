<?php
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * Refresh button is to view users
 * Ban and unban buttons are to have control of users
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->get(/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * Login request for admin account, to ensure correct authentication applied
 */ function (Request $request, Response $response) use ($app){
    session_start();
    $twigsArray = $app->getContainer()->get('sessionsModel')->getStatus();

    if (!isset($_SESSION['Logged_in']) || !$_SESSION['Logged_in'] || !isset($_SESSION['USERNAME'])) // If not logged in (including malformed ways) redirect to login page.
    {
        return $this->response->withRedirect('/login');
    } elseif ($_SESSION['USERNAME'] != 'Administrator') { // If not the administrator is redirected to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

    $sql = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');
    $users = $sql->allUsersQuery($db_login['database_settings']); // (Lazy) first population of table.
    if ($users > 0) {
        $twigsArray['userresults'] = $users;
    }

    return $this->view->render($response, 'adminmanageusers.html.twig', $twigsArray);
});
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * This is to refresh user log on admin pannel, user data from database should be updated when requested.
 */
$app->get('/refresh-users', function (Request $request, Response $response) use ($app){
    session_start();

    $log = new Logger('logger');
    $log->pushHandler(new StreamHandler(ADMIN_LOG, Logger::INFO));

    if ($_SESSION['USERNAME'] != 'Administrator') { // If not administrator redirect to "Forbidden Error" page.
        return $this->response->withRedirect('/error/403');
    }

    $sql = $app->getContainer()->get('SQLQueries');
    $db_login = $app->getContainer()->get('settings');

    $users = $sql->allUsersQuery($db_login['database_settings']);
    if ($users === false)
    {
        $message= 'ERROR: Could not retrieve any users';
        $log->info($message);
    }
    else
    {
        $log->info('Administrator retrieved list of users');
    }

    //return generation
    $return = new SimpleXMLElement('<xml/>');

    if ($users >= 1)
    {
        $return->addChild('success', "true");
        $xml_results = $return->addChild('results');

        $i=0;
        foreach ($users as $user) {
            $user_row = $xml_results->addChild("Row_".$i++);

            $user_row->addChild("ID", $user['id']);
            $user_row->addChild("Username", $user['username']);
            $user_row->addChild("Phone", $user['phone']);
            $user_row->addChild("Join_Date", $user['join_date']);
            $user_row->addChild("Last_Login", $user['last_login_date']);
        }
    }
    else {
        $return->addChild('success', "false");
        $return->addChild('message', $message);
    }

    //return preperation
    header("Content-Type:text/xml");

    print($return->asXML());
    exit;
});
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * This is to ban a user.
 * Only done through administrators account, request from other account should redirect to error 403 for access to the request forbidden.
 */
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

    //this is in case of self ban, as prevention of banning the administrator account
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

    //return generations
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
/**
 * @param Request $request
 * @param Response $response
 * @return mixed
 * This is for making a request to unban a banned user.
 * Only done through administrators account, request from other account should redirect to error 403, for access to the request forbidden.
 */
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