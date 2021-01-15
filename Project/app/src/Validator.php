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
        if (preg_match('![()}{/#<>,[\]\\,\'|\x22]+!', $cleaned_user)===1) //If same as 1 then illegal char found.
        {
            $cleaned_user = false;
        }
        return [$cleaned_user, $cleaned_pass]; //No index name given, so index are 0 and 1 when used externally to this class.
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
        foreach ($tainted_telemetry['switch'] as $char)
        {
            if ($char != "0" or $char != "1")
            {
                return false;
            }
        }
        if ($tainted_telemetry['fan'] != "forward" or $tainted_telemetry['fan'] != "reverse")
        {
            return false;
        }
        try {
            $int_test1 = (int)$tainted_telemetry['heater'];
            $int_test2 = (int)$tainted_telemetry['keypad'];
            
            if (($int_test1 == $tainted_telemetry['heater']) != 1)
            {
                return false;
            }
            if (($int_test2 == $tainted_telemetry['keypad']) != 1)
            {
                return false;
            }
            
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function testExistence()
    {
        return "This class and method can be found";
    }
    
    public function validatePhoneNumber($tainted_number)
    {
        $result = false;
        $cleaned_number = $this->sanitiseString($tainted_number);
        if ($tainted_number == $cleaned_number)
        {
            if ($cleaned_number[0] == '+')
            {
                try {
                    $int_test = (int)substr($cleaned_number, 1);
                    if (($int_test == substr($cleaned_number, 1)) == 1)
                    {
                        $result = true;
                    }
                }
                catch (\Exception $e)
                {
                    //ignore
                }
            }
        }
    }


}
