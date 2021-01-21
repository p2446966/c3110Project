<?php
/**
 * these are the routes for the site
 */
$routes_dir = "routes/";

require $routes_dir . 'homepage.php';
require $routes_dir . 'error.php';
require $routes_dir . 'login.php';
require $routes_dir . 'register.php';
require $routes_dir . 'logout.php';

require $routes_dir . 'soap.php';
require $routes_dir . 'sendSMS.php';
require $routes_dir . 'adminpage.php';