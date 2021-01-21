<?php
/**
 * Class DatabaseWrapper
 * @package Telemetry
 * this is to show database telemetry
 * This class manages the database connection itself and ensures data capture
 */
/*
 *  @author : ctapp, ccunha
 */

namespace Telemetry;


class DatabaseWrapper
{

    /**
     * @var
     */
    public $database;

    /**
     * DatabaseWrapper constructor.
     * constructor for set up a class when it is initialized
     */
    public function __construct() {}

    /**
     * destruct method is called when there are no more references to an object that you created or when you force its deletion
     */
    public function __destruct() {}

    /**
     * @param $db_details
     * @return bool
     */
    public function establishConn($db_details)
    {
        /**
         * $app_settings = $app->getContainer->get('settings');
         * $DB = $app_settings['database_settings'];
        */

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

    /**
     *
     */
    public function disconnectConn()
    {
        $this->database->close();
    }


}
