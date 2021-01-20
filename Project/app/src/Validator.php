<?php
/**
 * Class Validator
 * @package Telemetry
 */
/*
 * incoming data downloaded from the m2m server
 * must be validated
 *
 * also could do validation for accounts if we want to do that
 */

namespace Telemetry;



class Validator
{

    /**
     * Validator constructor.
     */
    public function __construct(){}

    /**
     *
     */
    public function __destruct(){}

    //Callum's regex filter moved from accounts.php to here in seperate method

    /**
     * @param $tainted_parameters
     * @return bool[]
     */
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

    /**
     * @param $tainted_string
     * @return bool|mixed
     */
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
    /**
     * @param $tainted_telemetry
     * @return bool
     */
    public function validateTelemetry($tainted_telemetry)
    {
        $split_switch = str_split($tainted_telemetry['switch']);
        foreach ($split_switch as $char)
        {
            if (($char != "0") and ($char != "1"))
            {
                return false;
            }
        }
        if (($tainted_telemetry['fan'] != "forward" and ($tainted_telemetry['fan'] != "reverse")))
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
            
            return $tainted_telemetry;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * @return string
     */
    public function testExistence()
    {
        return "This class and method can be found";
    }

    /**
     * @param $tainted_number
     */
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
                        $result = substr($cleaned_number, 1); //true;
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
