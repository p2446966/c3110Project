<?php

/**
 * @author : ctapp
 * Class SoapWrapper
 * @package Telemetry
 * This is to extract information about service to be consumed from the WSDL.
 * Also, enables access web services over HTTP.
 */

namespace Telemetry;



class SoapWrapper
{

    /**
     * SoapWrapper constructor.
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
     * Soap Client
     * @return \SoapClient|string
     * provides a client for SOAP sever
     * protocol to access web service over HTTP.
     */
    public function createSoapClient()
    {
        $wsdl = WSDL;
        try
        {
            $soap_client_handle = new \SoapClient($wsdl, ['trace' => true, 'exceptions' => true]);

        }
        catch (\SoapFault $exception)
        {
            $soap_client_handle = 'Soap Error';
        }
        return $soap_client_handle;
    }

    /**
     * sendMessage
     *  - string username
     *  - string password
     *  - string deviceMSISDM (e.g. +441234567890)
     *  - string message
     *  - boolean deliveryReport
     *  - string mtBearer ("SMS")
     * This returns
     *  - int returnCode, indicates successful call
     *
     */
    public function sendSMS($soap_client,
    $username,
    $password,
    $deviceMSISDM,
    $message)
    {
        $call_result = null;
        if ($soap_client)
        {
            try
            {
                $call_result = $soap_client->__soapCall("sendMessage",
                    [
                        $username,
                        $password,
                        $deviceMSISDM,
                        $message,
                        false,
                        "SMS"
                    ]
                );
                $call_result = true;
            }
            catch (\SoapFault $exception)
            {
                $call_result = $exception;
            }
        }
        return $call_result;
    }


    /**
     * readMessages
     *  - string username
     *  - string password
     *  - int count
     *  - string deviceMSISDN
     * this returns
     *  - string[] returnMsgs
     *      array of XML messages, possibly empty
     */
    public function recieveSMS($soap_client,
    $username,
    $password,
    $count,
    $deviceMSISDN)
    {
        $call_result = null;
        if ($soap_client)
        {
            try
            {
                $call_result = $soap_client->__soapCall("peekMessages",
                [
                    $username,
                    $password,
                    $count,
                    $deviceMSISDN
                ]);
            }
            catch (\SoapFault $exception)
            {
                $call_result = $exception;
            }
        }
        return $call_result;
    }

}
