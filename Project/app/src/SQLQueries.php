<?php

/*
 *  @author : ctapp, ccunha
 *
 *  This class contains the statements to pass to the database
 */

namespace Telemetry;


class SQLQueries extends DatabaseWrapper
{
    private $getUser = "SELECT * FROM users WHERE username=?";
    private $addUser = "INSERT INTO users (id, username, phone, password) VALUES (?, ?, ?, ?)";
    private $addTelemetry = "INSERT INTO telemetry (source, dest, recv_time, switch, fan, heater, keypad)
VALUES (?, ?, ?, ?, ?, ?, ?)";
    private $getTelemetry = "SELECT * FROM telemetry WHERE source=?";
    private $getUsers = "SELECT * FROM users WHERE username != 'Administrator'";
    private $banUser = "UPDATE users SET password = NULL WHERE username=?";
    private $unbanUser = "UPDATE users SET password = '$2y$10$.e7xRE9kVyOa9Xc/7v4Z1OgfaqQlB7zMSeycbycSXZuKKRP4ik7Ee' 
WHERE username=? AND password <=> NULL";// Change password to "unbanned" for username ? only if banned.

    public function __construct() {}
    public function __destruct() {}

    public function loginQuery($db_details, $username, $password)
    {
        $success = false;
        $this->establishConn($db_details); //establish database connection using parent class

        $stmt = $this->database->prepare($this->getUser);
        $stmt->bind_param("s", $username);

        if (!$stmt) { //Return false if prepare failed.
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
        // Note from Callum: it has UUID() but in testing it always generated the same.
        // will try again once things are more stable.
        // note 2: still generated same results sorry. might be a length constraint of
        // the output :(
        $stmt->execute();

        //keeping errors
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
                $_SESSION['join_date'] = ''; // callum's note: this used to be php current join date, which would be the same from db. why not now? :)
                $_SESSION['last_login_date'] = '';
                $result = true;
                break;
            }
            case 1062: //duplicate entry
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

    //uses the message as array created by xmlparser, not object
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

        //could check for errors but im not sure what errors would come from this

        $stmt->execute();
        $stmt->free_result();
        $stmt->close();
        $this->disconnectConn();
        return true;
    }

    public function retrieveTelemetry($db_details)
    {
        $result = false;
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

    public function allUsersQuery($db_details) //All users except Administrators account.
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
