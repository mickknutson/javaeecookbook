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
* @version  CVS: $Id: Exception.php,v 1.4 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

/**
* Exception class for the Services_Blogging package.
* Extends the normal exception class to make it easy
* to distinguish between blogging and other exceptions
* via instanceof
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_Exception extends Exception
{
    /**
    * Creates a new Exception object
    *
    * @param string  $message Exception message
    * @param integer $code    Exception code
    */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, intval($code));
    }//public function __construct($message = null, $code = 0)
}//class Services_Blogging_Exception extends Exception
?>