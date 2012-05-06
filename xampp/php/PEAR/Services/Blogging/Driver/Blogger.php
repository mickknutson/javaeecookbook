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
* @version  CVS: $Id: Blogger.php,v 1.5 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Driver.php';
require_once 'Services/Blogging/Driver/Exception.php';
require_once 'Services/Blogging/MultipleBlogsInterface.php';
require_once 'Services/Blogging/XmlRpc.php';
require_once 'XML/RPC.php';

/**
* Blogger API implementation.
*
* This class implements the Blogger XML-RPC API described at
* http://www.blogger.com/developers/api/1_docs/
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_Driver_Blogger
    extends Services_Blogging_Driver
    implements Services_Blogging_MultipleBlogsInterface
{
    /**
    * Requests shall be sent to here
    */
    const XML_RPC_SERVER = 'http://plant.blogger.com';
    const XML_RPC_PATH   = '/api/RPC2';

    /**
    * Id of the blog to be used.
    * Some blogs support multiple blogs with one account.
    * @var int
    */
    protected $nBlogId = null;

    /**
    * Internal list with user data.
    * @var array
    */
    protected $userdata = array();

    const ERROR_UNKNOWN_TEMPLATE = 112;



    /**
    * Constructor for the Blogger class that authenticates the user and sets class
    * properties. It will return the userinfo if authentication was successful, an
    * exception if authentication failed. The username, password, path to the
    * XML-RPC client and server URI are passed as parameters.
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
            'user'  => $user,
            'pass'  => $pass,
            'server'=> $server,
            'path'  => $path,
            'rpc_user'  => new XML_RPC_Value($user, 'string'),
            'rpc_pass'  => new XML_RPC_Value($pass, 'string'),
            'rpc_blogid'=> new XML_RPC_Value('', 'string'),
            'rpc_key'   => new XML_RPC_Value('0123456789ABCDEF', 'string')
        );

        $authenticate = new XML_RPC_Message(
            'blogger.getUserInfo',
            array(
                $this->userdata['rpc_key'],
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass']
            )
        );

        $this->rpc_client = new XML_RPC_Client(
            $this->userdata['path'],
            $this->userdata['server']
        );

        //FIXME: store the userinfo somewhere and make it available
        $userInfo = Services_Blogging_XmlRpc::sendRequest(
            $authenticate, $this->rpc_client
        );
    }//public function __construct($userid, $pass, $server = null, $path = null)



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
            $request = new XML_RPC_Message('blogger.newPost',
                array(
                    $this->userdata['rpc_key'],
                    $this->userdata['rpc_blogid'],
                    $this->userdata['rpc_user'],
                    $this->userdata['rpc_pass'],
                    new XML_RPC_Value(
                        $post->{Services_Blogging_Post::CONTENT}, 'string'
                    ),
                    new XML_RPC_Value(true, 'boolean')
                )
            );
            $nPostId = Services_Blogging_XmlRpc::sendRequest(
                $request, $this->rpc_client
            );
            $post->setId($nPostId);
        } else {
            //edit the post; it already exists
            $request = new XML_RPC_Message('blogger.editPost',
                array(
                    $this->userdata['rpc_key'],
                    new XML_RPC_Value($post->id, 'string'),
                    $this->userdata['rpc_user'],
                    $this->userdata['rpc_pass'],
                    new XML_RPC_Value(
                        $post->{Services_Blogging_Post::CONTENT}, 'string'
                    ),
                    new XML_RPC_Value(true, 'boolean')
                )
            );
            Services_Blogging_XmlRpc::sendRequest($request, $this->rpc_client);
        }
    }//public function savePost(Services_Blogging_Post $post)



    /**
    * Delete a given post
    *
    * @param mixed $post Services_Blogging_Post object to delete,
    *                     or post id (integer) to delete
    *
    * @return boolean True if deleted, false if not.
    */
    public function deletePost($post)
    {
        if ($post instanceof Services_Blogging_Post) {
            $id = new XML_RPC_Value($post->id, 'string');
        } else {
            $id = new XML_RPC_Value($post, 'string');
        }

        $request = new XML_RPC_Message(
            'blogger.deletePost',
            array(
                $this->userdata['rpc_key'],
                $id,
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
                new XML_RPC_Value(true, 'boolean')
            )
        );

        $response = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );
        return (bool)$response;
    }//public function deletePost($post)



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
        return array(Services_Blogging_Post::CONTENT);
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
        return $strProperty == Services_Blogging_Post::CONTENT;
    }//public function isPostPropertySupported(..)



    /**
    * Sets the blog id to use (some blogging APIs support multiple
    * blogs with one account)
    *
    * @param int $nBlogId Id of the blog to use
    *
    * @return void
    */
    public function setBlogId($nBlogId)
    {
        $this->nBlogId                = $nBlogId;
        $this->userdata['rpc_blogid'] = new XML_RPC_Value($nBlogId, 'string');
    }//public function setBlogId($nBlogId)



    /**
    * Returns the id of the currently used blog.
    *
    * @return int Blog id
    */
    public function getBlogId()
    {
        return $this->nBlogId;
    }//public function getBlogId()



    /**
    * Returns an array of blogs for that account.
    *
    * @return array Array of Services_Blogging_Blog objects
    */
    public function getBlogs()
    {
        $request = new XML_RPC_Message(
            'blogger.getUsersBlogs',
            array(
                $this->userdata['rpc_key'],
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass']
            )
        );

        $blogs = Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );

        $arBlogs = array();
        foreach ($blogs as $blog) {
            $arBlogs[] = new Services_Blogging_Blog(
                $blog['blogid'], $blog['blogName'], $blog['url']
            );
        }
        return $arBlogs;
    }//public function getBlogs()



    //{{{ getTemplate()


    /**
    * Implements the blogger.getTemplate() method. The BlogID of the blog for
    * which the template must be retrieved and the template type are passed as
    * parameters.
    * The template type is usually one of 'main' or 'archiveIndex'.
    * The template in HTML format is returned.
    *
    * A template is the HTML code that represents the format of your blog. It is
    * best to first examine the code that is returned by using this method;
    * modifying it to suit your requirements, and then updating the template
    * using the setTemplate() method.
    *
    * @param string $tempType The type of template to retrived. Usually either
    *                          'main' or 'archiveIndex'.
    *
    * @return string The template in HTML form.
    */
    public function getTemplate($tempType)
    {
        if ($tempType != 'main' && $tempType != 'archiveIndex') {
            throw new Services_Blogging_Driver_Exception(
                'Unknown template "' . $tempType . '"',
                self::ERROR_UNKNOWN_TEMPLATE
            );
        }

        $request = new XML_RPC_Message(
            'blogger.getTemplate',
            array(
                $this->userdata['rpc_key'],
                $this->userdata['rpc_blogid'],
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
                new XML_RPC_Value($tempType, 'string')
            )
        );
        return Services_Blogging_XmlRpc::sendRequest(
            $request, $this->rpc_client
        );
    }//public function getTemplate($tempType)



    /**
     * Implements the blogger.setTemplate() method. The BlogID of the blog for
     * which the template is to be set, the template type
     * (again: 'main' or 'archiveIndex')
     * and the actual template in the HTML format are passed as parameters.
     *
     * See the docblock for the getTemplate() to find out what a template is.
     *
     * @param string $tempType The type of the template being set. Either 'main'
     *                          or 'archiveIndex'.
     * @param string $template The actual template in the HTML format.
     *
     * @return  boolean Whether or not the template was set.
     */
    public function setTemplate($tempType, $template)
    {
        if ($tempType != 'main' && $tempType != 'archiveIndex') {
            throw new Services_Blogging_Driver_Exception(
                'Unknown template "' . $tempType . '"',
                self::ERROR_UNKNOWN_TEMPLATE
            );
        }

        $request = new XML_RPC_Message(
            'blogger.setTemplate',
            array(
                $this->userdata['rpc_key'],
                $this->userdata['rpc_blogid'],
                $this->userdata['rpc_user'],
                $this->userdata['rpc_pass'],
                new XML_RPC_Value($tempType, 'string'),
                new XML_RPC_Value($template, 'string')
            )
        );
        return Services_Blogging_XmlRpc::sendRequest($request, $this->rpc_client);
    }//public function setTemplate($tempType, $template)



    /**
    * Returns an array of supported Templates
    *
    * @return array Array of templates (strings)
    */
    public function getSupportedTemplates()
    {
        return array('main', 'archiveIndex');
    }//public function getSupportedTemplates()

}//class Services_Blogging_Driver_Blogger extends Services_Blogging_Driver
// implements Services_Blogging_MultipleBlogsInterface
?>