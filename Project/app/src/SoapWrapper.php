<?php

/**
 * @author : ctapp
 * date created 20/11/20
 */

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
 * readMessages
 *  - string username
 *  - string password
 *  - int count
 *  - string deviceMSISDN
 * returns
 *  - string[] returnMsgs
 *      array of XML messages, possibly empty
 */

namespace Telemetry;


class SoapWrapper
{
    public function __construct() {}
    public function __destruct() {}

    public function createSoapClient()
    {

    }

    public function performSoapCall()
    {

    }

}