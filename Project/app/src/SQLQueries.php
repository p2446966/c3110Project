<?php

namespace src;

// This class hosts static functions for database queries. It is not an object but is used as a library. Some of the logging could be improved.
class SQLQueries
{

    // For reuse and ease of editing.
    private static function dbConn()
    {
        $DB['HOST'] = 'localhost';
        $DB['USER'] = 'apiprojectmanager';
        $DB['PASS'] = '0t4KO0eyKTIijJro';
        $DB['NAME'] = 'apiproject';

        return $DB;
    }

    // Takes a monolog instance, username & password and prepares and executes that statement with basic error and log handling.
    public static function loginQuery($log, $username, $password)
    {
        $query_string  = "SELECT * ";
        $query_string .= "FROM users ";
        $query_string .= "WHERE username=?";

        // DB Connection Defined and setup.
        $DB = self::dbConn();
        $con = mysqli_connect($DB['HOST'], $DB['USER'], $DB['PASS'], $DB['NAME']);

        // Check connection and kill script if failed.
        if ($con->connect_error) {
            $log->alert("A database connection failure has occured... " . $con->connect_error);
            die("Error 2003: Login server connection down or refused. Please report to an admin"); //Ungraceful, could be improved easily with a handled return in the route.
        }


        if ($stmt = $con->prepare($query_string)) {

            /* bind parameters for markers */
            $stmt->bind_param("s", $username);

            /* execute query */
            $stmt->execute();

            /* Get the result */
            $result = $stmt->get_result();

            /* Get the number of rows */
            $num_of_rows = $result->num_rows;


            if ($num_of_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $results = $row;
                }

                if (password_verify($password, $results['password'])) {
                    /* free results and close connection*/
                    $stmt->free_result();
                    $stmt->close();
                    $con->close();

                    // Important! This is critical. Here we set the session variables while we have SQL data. Anything in the database should be added here if desired to be accessible in session.
                    // never set password in session var. Session var is a file stored in the file system too. Least sensitive data.
                    $_SESSION['UID'] = $results['id'];
                    $_SESSION['USERNAME'] = $username;
                    $_SESSION['email'] = $results['email'];
                    $_SESSION['join_date'] = date( 'd/m/Y H:i:s', strtotime($results['join_date']));
                    $_SESSION['last_login_date'] = "todo"; // finish implementing proper last login date.

                    $log->info("Successful login by UID: " . $results['id']);

                    return "true";
                } else {
                    /* free results and close connection*/
                    $stmt->free_result();
                    $stmt->close();
                    $con->close();
                    require_once "logout.php";
                    return "Incorrect password. Please retry.";
                }
            }

            /* free results and close connection*/
            $stmt->free_result();
            $stmt->close();
            $con->close();
            require_once "logout.php";
            return "Incorrect login details. Please retry.";
        }
        require_once "logout.php";
        $log->critical("Unidentified error in critical section... Couldn't prepare statement Dumping variable" . var_dump($stmt) ); // Not recommended.
        Return "There was an error processing those details.";
    }

    public static function registerQuery($log, $username, $password , $email)
    {
        //Query setup.
        $query_string  = "INSERT INTO users (id, username, email, password)";
        $query_string .= "VALUES (?, ?, ?, ?)";

        // DB Connection Defined and setup.
        $DB = self::dbConn();
        $con = mysqli_connect($DB['HOST'], $DB['USER'], $DB['PASS'], $DB['NAME']);

        // Check connection and kill script if failed.
        if ($con->connect_error) {
            $log->alert("A database connection failure has occured... " . $con->connect_error);
            die("Error 2003: Login server connection down or refused. Please report to an admin");
        }

        // Generate inital ID (Could be done in better ways like in MySQL
        $id = hexdec(uniqid());

        if ($stmt = $con->prepare($query_string)) {
            for ($i = 0; $i < 10; $i++) { // This loop is necessary encase we generate and ID that's already taken. If we magically did this more than 10 times we would fail to register our user.
                /* bind parameters for markers */
                $stmt->bind_param("isss", $id,$username, $email, $password);

                /* execute query */
                $stmt->execute();
                $timenow = $_SERVER['REQUEST_TIME']; // Just so we can get the most accurate time without SQL.

                /* Keeping errors */
                $sqlerror = $stmt->error;
                $sqlerrorno = $stmt->errno;

                /* free results and close connection*/
                $stmt->free_result();
                $stmt->close();
                $con->close();

                switch ($sqlerrorno) {
                    case (null || 0): // Success!
                        // Important! This is critical. Here we set the session variables while we have SQL data. Anything in the database should be added here if desired to be accessible in session.
                        $_SESSION['UID'] = $id;
                        $_SESSION['USERNAME'] = $username;
                        $_SESSION['email'] = $email;
                        $_SESSION['join_date'] = date('d/m/Y H:i:s', $timenow);
                        $_SESSION['last_login_date'] = "todo"; // finish implementing proper last login date.

                        return true; // If this return is used it will display 1.
                    case 1062: // Duplicate Entry (Already taken fields).
                        if (preg_match("/username'$/", $sqlerror)) {
                            require_once "logout.php";
                            return "Username taken";
                        } else if (preg_match("/email'$/", $sqlerror)) {
                            require_once "logout.php";
                            return "Email taken";
                        } else if (preg_match("/id'$/", $sqlerror)) {
                            $log->warning("User given duplicate. Trying again. Attempt (" . $i . "/10)");
                            $id = hexdec(uniqid());
                        } else {
                            require_once "logout.php";
                            $log->error("Unaccounted for duplication error in the database. " . $sqlerror);
                            Return "Error 1062: Please report to an admin! . $sqlerror";
                        }
                    default: // Any error no already cased.
                        require_once "logout.php";
                        $log->error("None duplication error given by MySQL. " . $sqlerror . " | " . $sqlerrorno);
                        Return "Error 500: Please report to an admin!";
                }
            }
        }
        $log->critical("Unidentified error in critical section... Couldn't prepare statement Dumping variable" . var_dump($stmt) ); // Not recommended.
        Return "There was an error processing those details.";
    }
}
