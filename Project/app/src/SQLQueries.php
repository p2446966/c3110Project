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
        $stmt->bind_param("isis", $id, $username, $phone, $password);
        //could use a do while in case generated id is taken
        //although i think sql has an autogenerate primary key anyway.....
        // Note from Callum: it has UUID() but in testing it always generated the same.
        // will try again once things are more stable.
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
                $_SESSION['join_date'] = '';
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

}
