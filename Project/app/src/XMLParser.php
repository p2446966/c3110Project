<?php

/**
 *  from m2mConnect user guide
 *  m2m XML messages with be wrapped in following
 *  <messagerx> indicates recieved message
 *  <sourcemsisdn> number message sent from
 *  <destinationmsisdn> number message was sent to
 *  <recievedtime> timestamp
 *  <bearer> either "GPRS" or "SMS"
 *  <messageref> not really sure
 *  <message> contents of message may also be in XML
 * Class XMLParser
 * @package Telemetry
 */

namespace Telemetry;



class XMLParser
{
    //unparsed array
    private $XMLData;
    //processed array
    private $processed_data;
    
    public function __construct()
    {
        $this->XMLData = [];
        $this->processed_data = [];
    }
    
    public function __destruct() {}

    //add unparsed data into unparsed array
    public function setXMLData($recieved_data)
    {
        if (!empty($recieved_data) && !is_null($recieved_data))
        {
            foreach ($recieved_data as $message)
            {
                array_push($this->XMLData, $message);
            }
        }
    }
    
    public function convertToArray($XMLObject)
    {
        $returned_array = [
            "sourcemsisdn" => $XMLObject->sourcemsisdn,
            "destinationmsisdn" => $XMLObject->destinationmsisdn,
            "receivedtime" => $XMLObject->receivedtime,
            "switch" => $XMLObject->message->switch,
            "fan" => $XMLObject->message->fan,
            "heater" => $XMLObject->message->heater,
            "keypad" => $XMLObject->message->keypad
        ];
        return $returned_array;
    }
    
    /**
    * XML format
    * <sourcemsisdn> number message was sent from
    * <destinationmsisdn> number message was sent to
    * <recievedtime> timestamp in format DD/MM/YYYY HH:MM:SS
    * <message>
    *       <group> our group, which is TCR
    *       <switch> 4 digits 1 or 0 indicating on or off for the 4 switches e.g. '0000'
    *       <fan> direction of fan, either 'forward' or 'reverse'
    *       <heater> temperature of heater
    *       <keypad> the last number entered
    */
    
    public function parseXML()
    {
        foreach ($this->XMLData as $message)
        {
            try
            {
                if (str_contains($message, '&lt;') == false)
                {
                    $breakdown = simplexml_load_string($message);
                    // TODO : implement check for user phone number in database
                    // TODO : also add phone number to database
                    //at the moment only messages sent from EE server to EE server can be recieved
                    if ($breakdown->message->group == 'TCR')
                    {
                        if ($breakdown->sourcemsisdn == $_SESSION['phone']) //or $breakdown->sourcemsisdn == '447817814149')
                        {
                            $array_form = $this->convertToArray($breakdown);
                            array_push($this->processed_data, $array_form);
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                //ignore
            }
        }
    }
    
    public function getData()
    {
        return $this->processed_data;
    }   
        
}
