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
 */

namespace Telemetry;


class XMLParser
{
    public function __construct() {}
    public function __destruct() {}

}