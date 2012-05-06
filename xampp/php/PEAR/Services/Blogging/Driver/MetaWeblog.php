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
* @version  CVS: $Id: MetaWeblog.php,v 1.6 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Driver/Exception.php';
require_once 'Services/Blogging/ExtendedDriver.php';
require_once 'Services/Blogging/Post.php';
require_once 'Services/Blogging/XmlRpc.php';
require_once 'XML/RPC.php';

/**
* metaWeblog API implementation.
* http://www.xmlrpc.com/metaWeblogApi
* http://www.movabletype.org/mt-static/docs/mtmanual_programmatic.html
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_Driver_MetaWeblog extends Services_Blogging_ExtendedDriver
{

    /**
    * Internal list with user data.
    * @var array
    */
    protected $userdata = array();

    protected $arSupportedPostProperties = array(
        Services_Blogging_Post::TITLE,
        Services_Blogging_Post::CONTENT,
        Services_Blogging_Post::DATE,
        Services_Blogging_Post::URL,
        Services_Blogging_Post::CATEGORIES,
    );



    /**
    * Constructor for the metaWeblog driver class.
    *
    * @param string $user   The username of the blog account.
    * @param string $pass   The password of the blog account.
    * @param string $server The URI of the server to connect to.
    * @param string $path   The path to the XML-RPC server script.
    *
    * @throws Services_Blogging_Exception  If authentication fails
    */
    public function __construct($user, $pass, $server, $path)
    {
        $this->userdata = array(
            'user'      => $user,
            'pass'      => $pass,
            'server'    => $server,
            'path'      => $path,
            'rpc_user'  => new XML_RPC_Value($user, 'string'),
            'rpc_pass'  => new XML_RPC_Value($pass, 'string'),
            'rpc_blogid'=> new XML_RPC_Value($user, 'string'),
        );

        $this->rpc_client = new XML_RPC_Client(
            $this->userdata['path'],
            $this->userdata['server']
        );
        //$this->rpc_client->setDebug(true);
    }//public function __construct($userid, $pass, $server, $path)



    /**
    * Save a new post into the blog.
    *
    * @param Services_Blogging_Post $post Post object to put online
    *
    * @return void
    *
    * @throws Services_Blogging_Exception If an error occured
    */
    public function savePost(Services_Blogging_Post $post)
    {
        if ($post->id === null) {
            //post is new and has no Id => create new one
            $request = new XML_RPC_Message('metaWeblog.newPost',
                array(
                    $this->userdata['rpc_blogid'],
                    $this->userdata['rpc_user'],
                    $this->userdata['rpc_pass'],
                    self::convertPostToStruct($post),
                    new XML_RPC_Value(true, 'boolean')
                )
            );
            $nPostId = Services_Blogging_XmlRpc::sendRequest(
                $request, $this->rpc_client
            );
            $post->setId($nPostId);
        } else {
            //edit the post; it already exists
            $request = new XML_RPC_Message('metaWeblog.editPost',
                array(
                    new XML_RPC_Value($post->id, 'string'),
                    $this->userdata['rpc_user'],
                    $this->userdata['rpc_pass'],
                    self::convertPostToStruct($post),
                    new XML_RPC_Value(true, 'boolean')
                )
            );
            Services_Blogging_XmlRpc::sendRequest($request, $this->rpc_client);
        }
    }//public function savePost(Services_Blogging_Post $post)



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
    public function getPost($id)
    {
        $request = new XML_RPC_Message('metaWeblog.getPost',
            array(
                new XML_RPC_Value($id, 'int'),
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
            )
        );

        $arData = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );
        return $this->convertStructToPost($arData);
    }//public function getPost($id)



    /**
    * Delete a given post.
    * The deletePost method in metaWeblog is just
    *  an alias to the deletePost blogger method
    *
    * @param mixed $post Services_Blogging_Post object to delete,
    *                     or post id (integer) to delete
    *
    * @return boolean True if deleted, false if not.
    */
    public function deletePost($post)
    {
        if (!($post instanceof Services_Blogging_Post)) {
            $nPostId = $post;
            $post    = new Services_Blogging_Post();
            $post->setId($nPostId);
        }

        $request = new XML_RPC_Message('metaWeblog.deletePost',
            array(
                //dummy API key
                new XML_RPC_Value('0123456789ABCDEF', 'string'),
                new XML_RPC_Value($post->id, 'int'),
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
                new XML_RPC_Value(true, 'boolean')
            )
        );
        Services_Blogging_XmlRpc::sendRequest($request, $this->rpc_client);
    }//public function deletePost($post)



    /**
    * Returns an array of recent posts as Services_Blogging_Post objects
    *
    * @param int $number The number of posts to be retrieved.
    *                     Defaults to 15
    *
    * @return Array An array of objects of the Services_Blogging_Post class that
    *                correspond to the number of posts requested.
    */
    public function getRecentPosts($number = 15)
    {
        $request = new XML_RPC_Message('metaWeblog.getRecentPosts',
            array(
                $this->userdata['rpc_blogid'],
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
                new XML_RPC_Value($number, 'int')
            )
        );

        $arData = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );

        $arPosts = array();
        foreach ($arData as $data) {
            $post               = $this->convertStructToPost($data);
            $arPosts[$post->id] = $post;
        }
        return $arPosts;
    }//public function getRecentPosts($number = 15)



    /**
    * The getRecentPostTitles method is intended to retrieve the given number of
    * posts titles from a blog.
    * The posts themselves can be retrieved with getPost() or getRecentPosts().
    *
    * There is no direct getRecentPostTitles method in metaWeblog. So
    * we internally call getRecentPosts() and strip out ids and titles of
    * the post. So this method is slow here, because all post data needs
    * to be transmitted.
    *
    * @param int $number The number of posts to be retrieved.
    *
    * @return Array An array of int => strings representing the
    *                post ids (key) and their title (value).
    */
    public function getRecentPostTitles($number = 15)
    {
        $arPosts  = $this->getRecentPosts($number);
        $arTitles = array();
        foreach ($arPosts as $post) {
            $arTitles[$post->id] = $post->{Services_Blogging_Post::TITLE};
        }
        return $arTitles;
    }//public function getRecentPostTitles($number = 15)



    /**
    * Returns an array of strings thay define
    * the properties that a post to this blog may
    * have.
    *
    * @param string $strPostType Type of post to create.
    *
    * @return array Array of strings
    *
    * @see getSupportedPostTypes()
    */
    public function getSupportedPostProperties($strPostType = 'post')
    {
        return $this->arSupportedPostProperties;
    }//public function getSupportedPostProperties(..)



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
    public function isPostPropertySupported($strProperty, $strPostType = 'post')
    {
        return in_array($strProperty, $this->arSupportedPostProperties);
    }//public function isPostPropertySupported(..)



    /**
    * Converts a struct returned by the webservice to
    * a Services_Blogging_Post object
    *
    * @param array $arStruct Struct to convert
    *
    * @return Services_Blogging_Post Converted post
    */
    protected function convertStructToPost($arStruct)
    {
        $post = new Services_Blogging_Post($this);

        $post->{Services_Blogging_Post::CONTENT} = $arStruct['description'];
        $post->{Services_Blogging_Post::TITLE}   = $arStruct['title'];
        //0123456789012345678
        //20060514T09:19:33
        $post->{Services_Blogging_Post::DATE} = mktime(
            substr($arStruct['dateCreated'],  9, 2), //hour
            substr($arStruct['dateCreated'], 12, 2), //minute
            substr($arStruct['dateCreated'], 15, 2), //second
            substr($arStruct['dateCreated'],  4, 2), //month
            substr($arStruct['dateCreated'],  6, 2), //day
            substr($arStruct['dateCreated'],  0, 4)  //year
        );

        $post->{Services_Blogging_Post::URL} = $arStruct['link'];

        if (!isset($arStruct['categories'])) {
            $arStruct['categories'] = array();
        }
        $post->{Services_Blogging_Post::CATEGORIES} = $arStruct['categories'];
        $post->setId($arStruct['postid']);

        return $post;
    }//protected function convertStructToPost($arStruct)



    /**
    * Converts Services_Blogging_Post object to
    * an XML-RPC struct that can be sent to the server.
    *
    * @param Services_Blogging_Post $post Post object to convert
    *
    * @return void
    */
    protected function convertPostToStruct($post)
    {
        $time = $post->{Services_Blogging_Post::DATE};
        if ($time == ''  || $time == 0) {
            $time = time();
        }
        $categories = $post->{Services_Blogging_Post::CATEGORIES};
        if (!is_array($categories)) {
            $categories = array();
        } else {
            $catstr     = $categories;
            $categories = array();
            foreach ($catstr as $cat) {
                $categories[] = new XML_RPC_Value($cat, 'string');
            }
        }

        return new XML_RPC_Value(
            array(
                'categories'  => new XML_RPC_Value($categories, 'array'),
                'dateCreated' => new XML_RPC_Value(
                    date('Ymd\\TH:i:s', $time), 'dateTime.iso8601'
                ),
                'description' => new XML_RPC_Value(
                    $post->{Services_Blogging_Post::CONTENT}, 'string'
                ),
                'title'       => new XML_RPC_Value(
                    $post->{Services_Blogging_Post::TITLE}, 'string'
                )
            ),
            'struct'
        );
    }//protected function convertPostToStruct($post)

}//class Services_Blogging_Driver_MetaWeblog extends Services_Blogging_ExtendedDriver
?>