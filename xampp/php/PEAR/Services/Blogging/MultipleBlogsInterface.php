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
* @version  CVS: $Id: MultipleBlogsInterface.php,v 1.3 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Blog.php';

/**
* Inteface to implement if an driver supports multiple
* blogs with one account.
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
interface Services_Blogging_MultipleBlogsInterface
{
    /**
    * Sets the blog id to use (some blogging APIs support multiple
    *  blogs with one account)
    *
    * @param int $nBlogId Id of the blog to use
    *
    * @return void
    */
    public function setBlogId($nBlogId);



    /**
    * Returns the id of the currently used blog.
    *
    * @return int Blog id
    */
    public function getBlogId();



    /**
    * Returns an array of blogs for that account.
    *
    * @return array Array of Services_Blogging_Blog
    */
    public function getBlogs();

}//interface Services_Blogging_MultipleBlogsInterface
?>