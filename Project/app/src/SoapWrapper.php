<?php

/**
 * @author : ctapp
 * date created 20/11/20
 */

namespace Telemetry;


class SoapWrapper
{
    public function __construct() {}
    public function __destruct() {}

    public function createSoapClient()
    {
        $wdsl = WDSL;
        try
        {
            $soap_client_handle = new \SoapClient($wdsl, ['trace' => true, 'exceptions' => true]);

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
     *returns
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
            }
            catch (\SoapFault $exception)
            {
                $call_result = $exception;
            }
        }
        return $call_result;
    }


    /*
     * readMessages
     *  - string username
     *  - string password
     *  - int count
     *  - string deviceMSISDN
     * returns
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
                $call_result = $soap_client->__soapCall("readMessages",
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