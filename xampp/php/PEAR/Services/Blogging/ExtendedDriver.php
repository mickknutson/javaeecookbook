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
* @version  CVS: $Id: ExtendedDriver.php,v 1.3 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Driver.php';

/**
* A class extended from Services_Blogging that provides additional abstract
* functions not present in the original class. These methods have been
* primarily implemented by the metaWeblog API driver.
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
abstract class Services_Blogging_ExtendedDriver extends Services_Blogging_Driver
{

    /**
    * Error code: Username or password doesn't exist/are wrong
    */
    const ERROR_POSTDOESNTEXIST = 103;



    /**
    * The getPost method is intended to retrive a given post as an object of
    * the Services_Blogging_Post class; given the unique post id which is passed
    * as a parameter to the function.
    *
    * @param string $id The PostID of the post to be retrieved. (As
    *                    returned by newPost() defined in
    *                    Services_Blogging_driver).
    *
    * @return Services_Blogging_Post The elements of the post returned as an
    *                                object of the Services_Blogging_Post class.
    *
    * @throws Services_Blogging_Exception If the post does not exist
    */
    abstract public function getPost($id);



    /**
    * Returns an array of recent posts as Services_Blogging_Post objects
    *
    * @param int $number The number of posts to be retrieved.
    *                     Defaults to 15
    *
    * @return Array An array of objects of the Services_Blogging_Post class that
    *                correspond to the number of posts requested.
    */
    abstract public function getRecentPosts($number = 15);



    /**
    * The getRecentPostTitles method is intended to retrieve the given number of
    * posts titles from a blog.
    * The posts themselves can be retrieved with getPost() or getRecentPosts().
    *
    * @param int $number The number of posts to be retrieved.
    *
    * @return Array An array of int => strings representing the
    *                post ids (key) and their title (value).
    */
    abstract public function getRecentPostTitles($number = 15);


}//abstract class Services_Blogging_ExtendedDriver extends Services_Blogging_Driver
?>