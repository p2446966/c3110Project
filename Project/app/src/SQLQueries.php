<?php
/**
 * Class SQLQueries
 * @package Telemetry
 * This class contains the statements to pass to the database
 */


namespace Telemetry;



class SQLQueries extends DatabaseWrapper
{
    /**
     * @var string
     * this is to get the user info from username
     */
    private $getUser = "SELECT * FROM users WHERE username=?";
    /**
     * @var string
     * this is to add username, phone, and password values.
     */
    private $addUser = "INSERT INTO users (id, username, phone, password) VALUES (?, ?, ?, ?)";
    /**
     * @var string
     * this adds the telemetry data from server.
     */
    private $addTelemetry = "INSERT INTO telemetry (source, dest, recv_time, switch, fan, heater, keypad)
VALUES (?, ?, ?, ?, ?, ?, ?)";
    /**
     * @var string
     * this then gets telemetry data from server
     */
    private $getTelemetry = "SELECT * FROM telemetry WHERE source=?";
    /**
     * @var string
     * admin account request
     */
    private $getUsers = "SELECT * FROM users WHERE username != 'Administrator'";
    /**
     * @var string
     * allows to ban user using admin account
     */
    private $banUser = "UPDATE users SET password = NULL WHERE username=?";
    /**
     * @var string
     * allows to ban user using admin account
     */
    private $unbanUser = "UPDATE users SET password = '$2y$10$.e7xRE9kVyOa9Xc/7v4Z1OgfaqQlB7zMSeycbycSXZuKKRP4ik7Ee' 
WHERE username=? AND password <=> NULL";// Change password to "unbanned" for username ? only if banned.

    /**
     * SQLQueries constructor.
     * constructor for set up a class when it is initialized.
     * the constructor will take in WSDL file name as parameter.
     * extract information about service to be consumed from the WSDL.
     */
    public function __construct() {}

    /**
     * Destructor called when an object is destructed.
     * PHP to call this function at the end of script.
     */
    public function __destruct() {}

    /**
     * @param $db_details
     * @param $username
     * @param $password
     * @return bool
     * this is for when a user submits their login details
     * Username, password checked with database details
     * connection to database is established
     * function binds the parameter to the SQL query and tell databse what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * returns true on sucsess
     * closes the database once done
     * returns false on failure to complete request.
     */
    public function loginQuery($db_details, $username, $password)
    {
        $success = false;
        $this->establishConn($db_details); //establish database connection using parent class

        $stmt = $this->database->prepare($this->getUser);
        $stmt->bind_param("s", $username);

        if (!$stmt) { //Return false if prepare has failed.
            return $success;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $num_of_rows = $result->num_rows;

        if ($num_of_rows > 0)
        {
            while ($row = $result->fetch_assoc())
            {
                $results = $row;
            }

            if (password_verify($password, $results['password']))
            {
                $_SESSION['UID'] = $results['id'];
                $_SESSION['USERNAME'] = $username;
                $_SESSION['phone'] = $results['phone'];
                $_SESSION['join_date'] = '';
                $_SESSION['last_login_date'] = '';
                $success = true;
            }
        }

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return $success;
    }

    /**
     * @param $db_details
     * @param $username
     * @param $password
     * @param $phone
     * @return bool|mixed|string
     * this for user registration
     * registered onto database ready for login procedure once logged out
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * closes the database once done
     */
    public function registerQuery($db_details, $username, $password, $phone)
    {
        $result = false;
        $this->establishConn($db_details);
        $id = hexdec(uniqid());
        $query = $this->addUser;

        $stmt = $this->database->prepare($query);
        $stmt->bind_param("isss", $id, $username, $phone, $password);
        //could use a do while in case generated id is taken
        //although i think sql has an autogenerate primary key anyway.....
        // Has UUID() but in testing it always generated the same.
        // still generated same results. possibly a length constraint of
        // the output :(
        $stmt->execute();

        //keeping the errors
        $sqlerror = $stmt->error;
        $sqlerrorno = $stmt->errno;

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();

        switch ($sqlerrorno)
        {
            case (null || 0): //success, store in session
            {
                $_SESSION['UID'] = $id;
                $_SESSION['USERNAME'] = $username;
                $_SESSION['phone'] = $phone;
                $_SESSION['join_date'] = ''; // This used to be php current join date, which would be the same from the database
                $_SESSION['last_login_date'] = '';
                $result = true;
                break;
            }
            case 1062: //duplicate entry error
            {
                $result = ERROR_CODES['1062'];
                break;
            }
            default: //any other error
            {
                $result = ERROR_CODES['500'];
                break;
            }
        }
        return $result;
    }


    /**
     * @param $db_details
     * @param $message
     * @return bool
     * uses the message as array created by xmlparser, not object
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * closes the database once done
     */
    public function storeTelemetry($db_details, $message)
    {
        $result = false;
        $this->establishConn($db_details);
        $query = $this->addTelemetry;

        $stmt = $this->database->prepare($query);
        $stmt->bind_param("sssssss",
            $message['sourcemsisdn'],
            $message['destinationmsisdn'],
            $message['recievedtime'],
            $message['switch'],
            $message['fan'],
            $message['heater'],
            $message['keypad']);

        if (!$stmt) { return $result; }

        //can check for errors but is unlikely that any would come from this

        $stmt->execute();
        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return true;
    }

    /**
     * @param $db_details
     * @return array
     * this should retrieve all request telemetry data from database
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * closes the database once done
     */
    public function retrieveTelemetry($db_details)
    {
        $result = [];
        $this->establishConn($db_details);

        $stmt = $this->database->prepare($this->getTelemetry);
        $stmt->bind_param("s", $_SESSION['phone']);

        if (!$stmt) { return $result; }

        $stmt->execute();
        $returned = $stmt->get_result();
        $num_of_rows = $returned->num_rows;

        if ($num_of_rows > 0)
        {
            $result = [];
            while ($row = $returned->fetch_assoc())
            {
                array_push($result, $row);
            }
        }

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return $result;
    }

    /**
     * @param $db_details
     * @return array|bool
     * All users except Administrators account.
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * presents results
     * closes the database once done
     * returns false on failure to complete request
     */
    public function allUsersQuery($db_details)
    {
        $this->establishConn($db_details);

        $stmt = $this->database->prepare($this->getUsers);

        $stmt->execute();
        $result = $stmt->get_result();

        $num_of_rows = $result->num_rows;

        if ($num_of_rows > 0)
        {
            $results = array();
            while($row = $result->fetch_assoc())
            {
                $results[] = $row;
            }

            return $results;
        }

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return false;
    }

    /**
     * @param $db_details
     * @param $username
     * @return bool
     * this is to ban user through updating database to prevent logging on from baned user
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * returns true on success
     * closes the database once done
     * returns false on failure to complete request
     */
    public function banUserQuery($db_details, $username)
    {
        $this->establishConn($db_details);

        $stmt = $this->database->prepare($this->banUser);
        $stmt->bind_param("s", $username);

        $stmt->execute();

        if($stmt->affected_rows > 0){return true;}

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return false;
    }

    /**
     * @param $db_details
     * @param $username
     * @return bool
     * this for unbanning of banned users from database
     * connection to database is established
     * function binds the parameter to the SQL query and tell database what the parameters are
     * function frees up memory related to the result
     * then frees up memory related to a prepared statement subsequently calling to cancel any results still remaining
     * returns true on success
     * closes the database once done
     * returns false on failure to complete request
     */
    public function unbanUserQuery($db_details, $username)
    {
        $this->establishConn($db_details);

        $stmt = $this->database->prepare($this->unbanUser);
        $stmt->bind_param("s", $username);

        $stmt->execute();

        if($stmt->affected_rows > 0){return true;}

        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return false;
    }
}
