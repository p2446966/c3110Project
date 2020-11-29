<?php

/*
 *  @author : ctapp, ccunha
 *
 *  This class manages the database connection itself
 */

namespace Telemetry;


class DatabaseWrapper
{

    public $database;

    public function __construct() {}
    public function __destruct() {}

    public function establishConn()
    {
        $app_settings = $app->getContainer->get('settings');
        $DB = $app_settings['database_settings'];
        $this->database = mysqli_connect(
            $DB['HOST'],
            $DB['USER'],
            $DB['PASS'],
            $DB['NAME']
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