<?php
/**
* Part of the Services_Blogging package.
*
* PHP version 5
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version  CVS: $Id: XmlRpc.php,v 1.3 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Exception.php';
require_once 'XML/RPC.php';

/**
* XmlRpc helper methods for the blogging API
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_XmlRpc
{
    /**
    * The function that actually sends an XML-RPC request to the server, handles
    * errors accordingly and returns the appropriately decoded data sent as response
    * from the server.
    *
    * @param XML_RPC_Message $request An appropriately encoded XML-RPC message
    *                                  that needs to be sent as a request to the
    *                                  server.
    * @param XML_RPC_Client  $client  The XML-RPC client as which the request
    *                                  is to be sent.
    *
    * @return Array The appropriately decoded response sent by the server.
    */
    public static function sendRequest($request, $client)
    {
        $response = $client->send($request);
        if (!$response) {
            throw new Services_Blogging_Exception(
                'XML-RPC communication error: ' . $client->errstr
            );
        } else if ($response->faultCode() != 0) {
            throw new Services_Blogging_Exception(
                $response->faultString(),
                $response->faultCode()
            );
        }

        $value = XML_RPC_Decode($response->value());
        if (!is_array($value) || !isset($value['faultCode'])) {
            return $value;
        } else {
            throw new Services_Blogging_Exception(
                $value['faultString'], $value['faultCode']
            );
        }
    }//public static function sendRequest($request, $client)

}//class Services_Blogging_XmlRpc
?>