<?php
/**
* Class SessionsModel
 * @package Telemetry
 * session telemetry should show allow for account management
*/

namespace Telemetry;


class SessionsModel
{
    //SessionsModel constructor.
    /**
     * SessionsModel constructor.
     */
    public function __construct() {}

    // destruct method is called when  no more references to an object that are created or when you force its deletion

    /**
     * destruct method to be called when there are no more references to an object.
     * forces its deletion.
     * PHP to call this function at the end of script.
     *
     */
    public function __destruct() {}

    /**
     * @return array|string[]
     * get status code associated with the response
     * return the response status code
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