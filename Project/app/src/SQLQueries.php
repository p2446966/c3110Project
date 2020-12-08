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
    private $addUser = "INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)";

    public function __construct() {}
    public function __destruct() {}

    public function loginQuery($db_details, $username, $password)
    {
        $success = false;
        $this->establishConn($db_details); //establish database connection using parent class
        $query = $this->getUser . $username;
        $stmt = $this->database->prepare($query);

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
                $_SESSION['email'] = $results['email'];
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

    public function registerQuery($db_details, $username, $password, $email)
    {
        $result = false;
        $this->establishConn($db_details);
        $id = hexdec(uniqid());
        $query = $this->addUser;

        $stmt = $this->database->prepare($query);
        $stmt->bind_param("isss", $id, $username, $email, $password);
        //could use a do while in case generated id is taken
        //although i think sql has an autogenerate primary key anyway
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
                $_SESSION['email'] = $email;
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
