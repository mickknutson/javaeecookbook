<?php
/**
* Part of the Services_Blogging package.
*
* PHP version 5
*
* @category Services
* @package  Services_Blogging
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version  CVS: $Id: LiveJournal.php,v 1.5 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/ExtendedDriver.php';
require_once 'Services/Blogging/Driver/Exception.php';
require_once 'Services/Blogging/XmlRpc.php';
require_once 'XML/RPC.php';

/**
* LiveJournal API implementation.
*
* This class implements the LiveJournal XML-RPC API described at
* http://www.livejournal.com/doc/server/ljp.csp.xml-rpc.protocol.html
*
* @category Services
* @package  Services_Blogging
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_Driver_LiveJournal
    extends Services_Blogging_ExtendedDriver
{
    /**
    * Requests shall be sent to here
    */
    const XML_RPC_SERVER = 'http://www.livejournal.com';
    const XML_RPC_PATH   = '/interface/xmlrpc';

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
    );



    /**
    * Constructor for the LiveJournal driver.
    *
    * If $server and $path are set to NULL, the default
    *  blogger.com address is used.
    *
    * @param string $user   The username of the blog account.
    * @param string $pass   The password of the blog account.
    * @param string $server The URI of the server to connect to.
    * @param string $path   The path to the XML-RPC server script.
    *
    * @throws Services_Blogging_Exception If authentication fails
    */
    public function __construct($user, $pass, $server = null, $path = null)
    {
        if ($server === null) {
            $server = self::XML_RPC_SERVER;
            $path   = self::XML_RPC_PATH;
        }
        $this->userdata = array(
            'user'      => $user,
            'pass'      => $pass,
            'server'    => $server,
            'path'      => $path,
            'rpc_user'  => new XML_RPC_Value($user, 'string'),
            'rpc_pass'  => new XML_RPC_Value($pass, 'string'),
        );


        $this->rpc_client = new XML_RPC_Client(
            $this->userdata['path'],
            $this->userdata['server']
        );

        $authdata = $this->getAuthData();

        $value = new XML_RPC_Value(
            array(
                'username'       => $this->userdata['rpc_user'],
                'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                'auth_challenge' => new XML_RPC_Value(
                    $authdata['challenge'], 'string'
                ),
                'auth_response'  => new XML_RPC_Value(
                    $authdata['response'] , 'string'
                ),
                'clientversion'  => new XML_RPC_Value(
                    'PHP/Services_Blogging-0.2.3'
                )
            ),
            'struct'
        );

        $authenticate = new XML_RPC_Message('LJ.XMLRPC.login', array($value));
        Services_Blogging_XmlRpc::sendRequest($authenticate, $this->rpc_client);
    }//public function __construct($userid, $pass, $server = null, $path = null)



    /**
    * Creates md5 hash of the given string and converts it to hexadecimal
    * representation.
    *
    * @param string $string Some string value
    *
    * @return string md5-hashed hecadecimal representation
    */
    protected function md5hex($string)
    {
        $md5 = md5($string, true);//raw output
        $hex = '';
        for ($nC = 0; $nC < strlen($md5); $nC++) {
            $hex .= str_pad(dechex(ord($md5[$nC])), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }//protected function md5hex($string)



    /**
    * Returns the authentication data used for the challenge-repsonse
    * mechanism.
    *
    * @return array Array with authentican data.
    */
    protected function getAuthData()
    {
        //get challenge for authentication
        $authenticate = new XML_RPC_Message('LJ.XMLRPC.getchallenge', array());
        $response     = Services_Blogging_XmlRpc::sendRequest(
            $authenticate, $this->rpc_client
        );

        return array(
            'challenge' => $response['challenge'],
            'response'  => $this->md5hex(
                $response['challenge'] . $this->md5hex($this->userdata['pass'])
            )
        );
    }//protected function getAuthData()



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
        $authdata = $this->getAuthData();

        $time = $post->{Services_Blogging_Post::DATE};
        if ($time == ''  || $time == 0) {
            $time = time();
        }

        if ($post->id === null) {
            //post is new and has no Id => create new one
            $value = new XML_RPC_Value(
                array(
                    'username'       => $this->userdata['rpc_user'],
                    'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                    'auth_challenge' => new XML_RPC_Value(
                        $authdata['challenge'], 'string'
                    ),
                    'auth_response'  => new XML_RPC_Value(
                        $authdata['response'] , 'string'
                    ),

                    'subject'        => new XML_RPC_Value(
                        $post->{Services_Blogging_Post::TITLE}
                    ),
                    'event'          => new XML_RPC_Value(
                        $post->{Services_Blogging_Post::CONTENT}
                    ),
                    'lineendings'    => new XML_RPC_Value('pc'),

                    'year'           => new XML_RPC_Value(date('Y', $time), 'int'),
                    'mon'            => new XML_RPC_Value(date('n', $time), 'int'),
                    'day'            => new XML_RPC_Value(date('j', $time), 'int'),
                    'hour'           => new XML_RPC_Value(date('G', $time), 'int'),
                    'min'            => new XML_RPC_Value(date('i', $time), 'int'),
                ),
                'struct'
            );

            $request = new XML_RPC_Message('LJ.XMLRPC.postevent', array($value));

            $arData = Services_Blogging_XmlRpc::sendRequest(
                $request, $this->rpc_client
            );
            $post->setId($arData['itemid']);
            $post->{Services_Blogging_Post::URL} = $arData['url'];
        } else {
            //edit the post; it already exists
            $value = new XML_RPC_Value(
                array(
                    'username'       => $this->userdata['rpc_user'],
                    'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                    'auth_challenge' => new XML_RPC_Value(
                        $authdata['challenge'], 'string'
                    ),
                    'auth_response'  => new XML_RPC_Value(
                        $authdata['response'] , 'string'
                    ),

                    'itemid'         => new XML_RPC_Value($post->id, 'int'),

                    'subject'        => new XML_RPC_Value(
                        $post->{Services_Blogging_Post::TITLE}
                    ),
                    'event'          => new XML_RPC_Value(
                        $post->{Services_Blogging_Post::CONTENT}
                    ),
                    'lineendings'    => new XML_RPC_Value('pc'),

                    'year'           => new XML_RPC_Value(date('Y', $time), 'int'),
                    'mon'            => new XML_RPC_Value(date('n', $time), 'int'),
                    'day'            => new XML_RPC_Value(date('j', $time), 'int'),
                    'hour'           => new XML_RPC_Value(date('G', $time), 'int'),
                    'min'            => new XML_RPC_Value(date('i', $time), 'int'),
                ),
                'struct'
            );

            $request = new XML_RPC_Message('LJ.XMLRPC.editevent', array($value));

            $arData = Services_Blogging_XmlRpc::sendRequest(
                $request, $this->rpc_client
            );
        }
    }//public function savePost(Services_Blogging_Post $post)



    /**
    * Delete a given post.
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
        /**
        * In LiveJournal, posts are deleted by emptying
        * some fields
        */
        $post->{Services_Blogging_Post::CONTENT} = '';
        $post->{Services_Blogging_Post::TITLE}   = '';
        $post->{Services_Blogging_Post::DATE}    = 0;

        return $this->savePost($post);
    }//public function deletePost($post)



    /**
    * The getPost method is intended to retrieve a given post as an object of
    * the Services_Blogging_Post class; given the unique post id which is passed
    * as a parameter to the function.
    *
    * @param string $id The PostID of the post to be retrieved.
    *
    * @return Services_Blogging_Post The elements of the post returned as an
    *                                object of the Services_Blogging_Post class.
    *
    * @throws Services_Blogging_Exception If the post does not exist
    */
    public function getPost($id)
    {
        $authdata = $this->getAuthData();

        $value = new XML_RPC_Value(
            array(
                'username'       => $this->userdata['rpc_user'],
                'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                'auth_challenge' => new XML_RPC_Value(
                    $authdata['challenge'], 'string'
                ),
                'auth_response'  => new XML_RPC_Value(
                    $authdata['response'] , 'string'
                ),

                'selecttype'     => new XML_RPC_Value('one', 'string'),
                'itemid'         => new XML_RPC_Value($id, 'int')
            ),
            'struct'
        );

        $request = new XML_RPC_Message('LJ.XMLRPC.getevents', array($value));

        $arData = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );
        if (count($arData['events']) == 0) {
            throw new Services_Blogging_Driver_Exception(
                'Post does not exist', self::ERROR_POSTDOESNTEXIST
            );
        }

        return $this->convertStructToPost(reset($arData['events']));
    }//public function getPost($id)



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
        if ($number > 50) {
            $number = 50;
        }

        $authdata = $this->getAuthData();
        $value    = new XML_RPC_Value(
            array(
                'username'       => $this->userdata['rpc_user'],
                'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                'auth_challenge' => new XML_RPC_Value(
                    $authdata['challenge'], 'string'
                ),
                'auth_response'  => new XML_RPC_Value(
                    $authdata['response'] , 'string'
                ),

                'selecttype'     => new XML_RPC_Value('lastn', 'string'),
                'howmany'        => new XML_RPC_Value($number, 'int')
            ),
            'struct'
        );
        $request  = new XML_RPC_Message('LJ.XMLRPC.getevents', array($value));

        $arData = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );

        $arPosts = array();
        foreach ($arData['events'] as $event) {
            $post               = $this->convertStructToPost($event);
            $arPosts[$post->id] = $post;
        }

        return $arPosts;
    }//public function getRecentPosts($number = 15)



    /**
    * The getRecentPostTitles method is intended to retrieve the given number of
    * post titles from a blog.
    * The posts themselves can be retrieved with getPost() or getPosts().
    *
    * @param int $number The number of posts to be retrieved.
    *
    * @return Array An array of int => strings representing the
    *                post ids (key) and their title (value).
    */
    public function getRecentPostTitles($number = 15)
    {
        if ($number > 50) {
            $number = 50;
        }

        $authdata = $this->getAuthData();

        $value = new XML_RPC_Value(
            array(
                'username'       => $this->userdata['rpc_user'],
                'auth_method'    => new XML_RPC_Value('challenge', 'string'),
                'auth_challenge' => new XML_RPC_Value(
                    $authdata['challenge'], 'string'
                ),
                'auth_response'  => new XML_RPC_Value(
                    $authdata['response'] , 'string'
                ),

                'selecttype'     => new XML_RPC_Value('lastn', 'string'),
                'howmany'        => new XML_RPC_Value($number, 'int'),
                'prefersubject'  => new XML_RPC_Value(true, 'boolean'),
                'truncate'       => new XML_RPC_Value(50, 'string'),
                'noprops'        => new XML_RPC_Value(true, 'boolean')
            ),
            'struct'
        );

        $request = new XML_RPC_Message('LJ.XMLRPC.getevents', array($value));

        $arData = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );

        $arTitles = array();
        foreach ($arData['events'] as $event) {
            $arTitles[$event['itemid']] = $event['event'];
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

        $post->{Services_Blogging_Post::CONTENT} = $arStruct['event'];
        $post->{Services_Blogging_Post::TITLE}   = $arStruct['subject'];
        //0123456789012345678
        //2006-05-13 21:42:00
        $post->{Services_Blogging_Post::DATE} = mktime(
            substr($arStruct['eventtime'], 11, 2), //hour
            substr($arStruct['eventtime'], 14, 2), //minute
            substr($arStruct['eventtime'], 17, 2), //second
            substr($arStruct['eventtime'],  5, 2), //month
            substr($arStruct['eventtime'],  8, 2), //day
            substr($arStruct['eventtime'],  0, 4)  //year
        );

        $post->{Services_Blogging_Post::URL} = $arStruct['url'];
        $post->setId($arStruct['itemid']);

        return $post;
    }//protected function convertStructToPost($arStruct)

}//class Services_Blogging_Driver_Blogger
?>