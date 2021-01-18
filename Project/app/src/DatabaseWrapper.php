<?php
/**
 * Class DatabaseWrapper
 * @package Telemetry
 */
/*
 *  @author : ctapp, ccunha
 *
 *  This class manages the database connection itself and ensures data capture
 */

namespace Telemetry;


class DatabaseWrapper
{

    public $database;

    public function __construct() {}
    public function __destruct() {}

    public function establishConn($db_details)
    {
        //$app_settings = $app->getContainer->get('settings');
        //$DB = $app_settings['database_settings'];
        $this->database = mysqli_connect(
            $db_details['HOST'],
            $db_details['USER'],
            $db_details['PASS'],
            $db_details['NAME']
        );

        if ($this->database->connect_error)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function disconnectConn()
    {
        $this->database->close();
    }


}
