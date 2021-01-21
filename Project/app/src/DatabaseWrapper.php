<?php
/**
 * Class DatabaseWrapper
 * @package Telemetry
 * this is to show database telemetry
 * This class manages the database connection itself and ensures data capture
 */


namespace Telemetry;



class DatabaseWrapper
{

    /**
     * @var
     * access database
     */
    public $database;

    /**
     * DatabaseWrapper constructor.
     * constructor for set up of a class when it is initialized.
     */
    public function __construct() {}

    /**
     * destruct method to be called when there are no more references to an object.
     * forces its deletion.
     * PHP to call this function at the end of script.
     */
    public function __destruct() {}

    /**
     * @param $db_details
     * @return bool
     * provides connection to the MySQL database
     *
     */
    public function establishConn($db_details)
    {
        /**
         * $app_settings = $app->getContainer->get('settings');
         * $DB = $app_settings['database_settings'];
         * This function opens a new connection to the MySQL server
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
     * function will be called at the end of the script to disconnect from database
     * close database
     */
    public function disconnectConn()
    {
        $this->database->close();
    }


}
