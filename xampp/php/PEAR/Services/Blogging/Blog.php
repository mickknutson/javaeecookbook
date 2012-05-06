<?php
/**
* Part of the Services_Blogging package.
*
* PHP version 5
*
* @category Services
* @package  Services_Blogging
* @author   Christian Weiske <cweiske@php.net>
* @author   Anant Narayanan <anant@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version  CVS: $Id: Blog.php,v 1.3 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

/**
* Blog object. Used when multiple blogs are supported by
* one account.
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
* @see      Services_Blogging_MultipleBlogsInterface
*/
class Services_Blogging_Blog
{
    /**
    * Exception code when the property is not supported.
    */
    const ERROR_INVALID_PROPERTY = 181;

    /**
    * Values/properties supported by the blogging system.
    *
    * @var array
    */
    protected $values = array(
        'id'    => null,
        'name'  => null,
        'url'   => null
    );



    /**
    * Creates a new blog instance.
    *
    * @param string $id   ID of the blog
    * @param string $name Name of the blog
    * @param string $url  URL of the blog
    */
    public function __construct($id = null, $name = null, $url = null)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->url  = $url;
    }//public function __construct($id = null, $name = null, $url = null)



    /**
    * Set a property value.
    *
    * @param string $strProperty Property name
    * @param mixed  $value       Value of property
    *
    * @return void
    */
    public function __set($strProperty, $value)
    {
        /*
        if (!isset($this->values[$strProperty])) {
            require_once 'Services/Blogging/Exception.php';
            var_dump($this->values);
            echo 'Invalid property "' . $strProperty . '"';
            throw new Services_Blogging_Exception(
                'Invalid property "' . $strProperty . '"',
                self::ERROR_INVALID_PROPERTY
            );
        }
        */
        $this->values[$strProperty] = $value;
    }//public function __set($strProperty, $value)



    /**
    * Returns value of a property
    *
    * @param string $strProperty Property name
    *
    * @return mixed Property value
    */
    public function __get($strProperty)
    {
        /*
        if (!isset($this->values[$strProperty])) {
            require_once 'Services/Blogging/Exception.php';
            throw new Services_Blogging_Exception(
                'Invalid property "' . $strProperty . '"',
                self::ERROR_INVALID_PROPERTY
            );
        }
        */
        return $this->values[$strProperty];
    }//public function __get($strProperty)

}//class Services_Blogging_Blog
?>