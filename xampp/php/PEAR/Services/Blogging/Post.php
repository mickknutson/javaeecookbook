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
* @version  CVS: $Id: Post.php,v 1.4 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Driver.php';

/**
* Generic blog post object.
*
* This class defines a generic post object that may be used
* to send or receive data in a common format to blogs.
*
* @category Services
* @package  Services_Blogging
* @author   Anant Narayanan <anant@php.net>
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*/
class Services_Blogging_Post
{
    /**
    * Array with property values.
    *
    * @var array
    */
    protected $values = array(
        'id'    => null
    );

    /**
    * The driver that will be used (optional).
    * If set, the __set and __get methods can check if the
    * properties are allowed or not.
    *
    * @var Services_Blogging_Driver
    */
    protected $driver = null;



    /**
    * Class constants that help define the post.
    */

    /**
    * Title of the blog post entry.
    * string
    */
    const TITLE = 'title';

    /**
    * Text/content for the entry.
    */
    const CONTENT = 'content';

    /**
    * Date at which the post shall be published.
    * If set, it has to be a unix timestamp (int)
    */
    const PUBDATE = 'publishdate';

    /**
    * Date at which the post has been written.
    * If set, it has to be a unix timestamp (int)
    */
    const DATE = 'date';

    /**
    * Where to find the entry. Read-only because
    * the blogging service determines it.
    */
    const URL = 'url';

    /**
    * Array of categories (tags) to use.
    */
    const CATEGORIES = 'categories';

    /**
     * String (url) when you post a link.
     */
    const LINK = 'link';

    /**
     * String (e.g. url of a picture, or a source of a quote)
     */
    const SOURCE = 'source';

    /**
     * String binary of a file upload.
     */
    const DATA = 'data';

    /**
     * String.
     */
    const CAPTION = 'caption';

    /**
    * Not used yet
    */
    const AUTHOR    = 'author';
    const CATEGORY  = 'categories';
    const COMMENTS  = 'comments';
    const ENCLOSURE = 'enclosure';
    const GUID      = 'guid';


    /**
    *  The property isn't supported by the driver.
    */
    const ERROR_UNSUPPORTED_PROPERTY = 401;



    /**
    * Services_Blogging_Post constructor.
    *
    * @param Services_Blogging_Driver $driver Optional driver object
    *                                          for further checks
    */
    public function __construct($driver = null)
    {
        $this->driver = $driver;
    }//public function __construct($driver = null)



    /**
    * Sets an object property
    *
    * @param string $strProperty Name of property
    * @param mixed  $value       New value
    *
    * @return void
    */
    public function __set($strProperty, $value)
    {
        if ($strProperty == 'id') {
            require_once 'Services/Blogging/Exception.php';
            throw new Services_Blogging_Exception(
                '"id" may be set via setId() only'
            );
        } else if ($this->driver !== null
            && !$this->driver->isPostPropertySupported($strProperty)) {
            require_once 'Services/Blogging/Exception.php';
            throw new Services_Blogging_Exception(
                'Post property "' . $strProperty
                 . '" is not supported by this driver',
                self::ERROR_UNSUPPORTED_PROPERTY
            );
        }

        $this->values[$strProperty] = $value;
    }//public function __set($strProperty, $value)



    /**
    * Retrieves an object property
    *
    * @param string $strProperty Name of property
    *
    * @return mixed Property value
    */
    public function __get($strProperty)
    {
        if ($strProperty == 'id') {
            return $this->values['id'];
        } else if ($this->driver !== null
            && !$this->driver->isPostPropertySupported($strProperty)) {
            require_once 'Services/Blogging/Exception.php';
            throw new Services_Blogging_Exception(
                'Post property "' . $strProperty
                 . '" is not supported by this driver',
                self::ERROR_UNSUPPORTED_PROPERTY
            );
        } else if (!isset($this->values[$strProperty])) {
            return null;
        }
        return $this->values[$strProperty];
    }//public function __get($strProperty)



    /**
    * Sets the post id. This method should only be
    * used by the driver implementations that just uploaded
    * a post to the blog, and it got an id now.
    *
    * @param int $id The blog post id
    *
    * @return void
    */
    public function setId($id)
    {
        $this->values['id'] = $id;
    }//public function setId($id)



    /**
    * Set the driver object
    *
    * @param Services_Blogging_Driver $driver Driver object that is used
    *                                          with this blog post
    *
    * @return void
    */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }//public function setDriver($driver)

}//class Services_Blogging_Post
?>