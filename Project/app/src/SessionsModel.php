<?php


namespace Telemetry;


class SessionsModel
{
    public function __construct() {}
    public function __destruct() {}

    public function getStatus()
    {
        if (isset($_SESSION['Logged_in']) && $_SESSION['Logged_in'])
        {
            return  [
            'username' => $_SESSION['USERNAME'],
            'loginFirstLink' => "#",
            'loginFirstText' => "Placeholder",
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