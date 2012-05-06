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
* @version  CVS: $Id: Driver.php,v 1.4 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Post.php';

/**
* A PHP interface to blogging APIs
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
abstract class Services_Blogging_Driver
{
    /**
    * Error code: Username or password doesn't exist/are wrong
    */
    const ERROR_USERIDPASS = 102;

    /**
    * Error code: Unsupported post type used in createPost()
    */
    const ERROR_WRONGPOSTTYPE = 103;



    /**
    * Save a new post into the blog.
    *
    * @param Services_Blogging_Post $post Post object to put online
    *
    * @return string The ID assigned to the post
    */
    abstract public function savePost(Services_Blogging_Post $post);



    /**
    * Delete a given post
    *
    * @param mixed $post Services_Blogging_Post object to delete,
    *                     or post id (integer) to delete
    *
    * @return boolean True if deleted, false if not.
    */
    abstract public function deletePost($post);



    /**
    * Returns an array of strings thay define
    * the properties that a post to this blog may have.
    *
    * @param string $strPostType Type of post to create.
    *
    * @return array Array of strings
    *
    * @see getSupportedPostTypes()
    */
    abstract public function getSupportedPostProperties($strPostType = 'post');



    /**
    * Checks if the given property name/id is supported
    * for this driver.
    *
    * @param string $strProperty Property name/id to check
    * @param string $strPostType Type of post to create.
    *
    * @return boolean If the property is supported
    *
    * @see getSupportedPostTypes()
    */
    abstract public function isPostPropertySupported(
        $strProperty, $strPostType = 'post'
    );



    /**
    * Creates a new post object and returns that.
    * Automatically sets the driver object in the post.
    *
    * Needs to be overwritten by drivers supporting multiple post types.
    *
    * @param string $strPostType Type of post to create.
    *
    * @return Services_Blogging_Post New post object
    *
    * @throws Services_Blogging_Driver_Exception When an unsupported post
    *  type is used.
    *
    * @see getSupportedPostTypes()
    */
    public function createNewPost($strPostType = 'post')
    {
        //this does not make much sense for drivers with only one
        // post type, but it helps to keep apps consistent by
        // validating parameters
        $arSupportedTypes = $this->getSupportedPostTypes();
        if (!in_array($strPostType, $arSupportedTypes)) {
            throw new Services_Blogging_Driver_Exception(
                'Unsupported post type "' . $strPostType . '"',
                self::ERROR_WRONGPOSTTYPE
            );
        }

        //when overwriting this method, create the different post instances
        // here
        return new Services_Blogging_Post($this);
    }//public function createNewPost(..)



    /**
    * Returns an array of supported post types. Pass one of them
    * to createNewPost() to instantiate such a post object.
    *
    * Useful for drivers that support multiple post types like
    * normal post ("post"), video and such. Most drivers support posts
    * only.
    *
    * Needs to be overwritten by drivers supporting post types.
    *
    * @return array Array of strings (post types)
    */
    public function getSupportedPostTypes()
    {
        return array('post');
    }//public function getSupportedPostTypes()

}//abstract class Services_Blogging_Driver
?>