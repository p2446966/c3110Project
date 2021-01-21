<?php
/**
* Class SessionsModel
 * @package Telemetry
*/

namespace Telemetry;


class SessionsModel
{
    /**
     * SessionsModel constructor.
     */
    public function __construct() {}

    /*
     * destruct method is called when there are no more references to an object that you created or when you force its deletion
     */
    public function __destruct() {}

    /**
     * @return array|string[]
     */
    public function getStatus()
    {
        if (isset($_SESSION['Logged_in']) && $_SESSION['Logged_in'])
        {
            return  [
            'username' => $_SESSION['USERNAME'],
            'loginFirstLink' => "/manageusers",
            'loginFirstText' => "Admin Panel",
            'loginSecondLink' => "/logout",
            'loginSecondText' => "Logout",
        ];
        } else {
            return [
            'username' => "Accounts",
            'loginFirstLink' => "/login",
            'loginFirstText' => "Login",
            'loginSecondLink' => "/register",
            'loginSecondText' => "Register",
        ];
        }
    }
}