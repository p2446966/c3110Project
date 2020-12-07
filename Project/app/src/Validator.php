<?php

/*
 * incoming data downloaded from the m2m server
 * must be validated
 *
 * also could do validation for accounts if we want to do that
 */

namespace Telemetry;


class Validator
{

    public function __construct(){}
    public function __destruct(){}

    //Callum's regex filter moved from accounts.php to here in seperate method
    public function validateInput($tainted_parameters)
    {
        //sanitise both username and password
        $cleaned_user = $this->sanitiseString($tainted_parameters['username']);
        $cleaned_pass = $this->sanitiseString($tainted_parameters['password']);

        //filter username for invalid chars
        if (!preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $cleaned_user))
        {
            $cleaned_user = false;
        }
        return [$cleaned_user, $cleaned_pass];
    }

    //basic sanitiser, for user input and soap recieves
    public function sanitiseString($tainted_string)
    {
        $cleaned_string = false;
        if (!empty($tainted_string) && !is_null($tainted_string))
        {
            $sanitised_string = filter_var($tainted_string, FILTER_SANITIZE_STRING);
            return $sanitised_string;
        }
        return $cleaned_string;
    }

    //filters, sanitises and returns telemetry
    // switch : 4 digit 1 or 0
    // fan : 'forward' or 'reverse'
    // heater : integer
    // keypad : integer
    public function validateTelemetry($tainted_telemetry)
    {
        foreach ($tainted_telemetry->message->switch as $char)
        {
            if ($char != "0" or $char != "1")
            {
                return false;
            }
        }
        if ($tainted_telemetry->message->fan != "forward" or $tainted_telemetry->message->fan != "reverse")
        {
            return false;
        }
        if (!is_int($tainted_telemetry->message->heater))
        {
            return false;
        }
        if (!is_int($tainted_telemetry->message->keypad))
        {
            return false;
        }
        return true;
    }

    public function testExistence()
    {
        return "This class and method can be found";
    }


}