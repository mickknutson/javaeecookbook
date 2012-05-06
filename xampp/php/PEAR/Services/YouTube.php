<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Client for YouTube API
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 *
 * @category   Services
 * @package    Services_YouTube
 * @author     Shin Ohno <ganchiku@gmail.com>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    0.2.0 $Id: YouTube.php,v 1.7 2006/10/21 09:31:41 shin Exp $
 * @see        http://www.youtube.com/dev
 * @since      0.1
 */

/**
 * Services_YouTube exception class
 */
require_once 'Services/YouTube/Exception.php';

/**
 * Services_YouTube
 *
 * @package Services_YouTube
 * @version 0.2.0
 * @author Shin Ohno <ganchiku@gmail.com>
 */
class Services_YouTube
{
    // {{{ class const
    /**
     * Version of this package
     */
    const VERSION = '0.2.0';

    /**
     * URL of the YouTube Server
     */
    const URL = 'www.youtube.com';
    /**
     * URI of the XML RPC path
     */
    const XMLRPC_PATH = '/api2_xmlrpc';

    /**
     * URI of the REST path
     */
    const REST_PATH = '/api2_rest';

    /**
     * Max number of the movie list per page
     */
    const VIDEO_PER_PAGE_MAX = 100;
    // }}}
    // {{{ class vars
    /**
     * Developer ID
     *
     * @var string
     * @access public
     */
    protected $developerId = null;

    /**
     * driver
     *
     * @var string
     * @access protected
     */
    protected $driver = 'rest';

    /**
     * Use cache
     * @var boolean
     * @access protected
     */
    protected $useCache = false;
    /**
     * cache_lite options
     *
     * @var array
     * @access protected
     */
    protected $cacheOptions = array();
    /**
     * format of the xml response
     *
     * @var string
     * @access protected
     */
    protected $responseFormat = 'object';
    // }}}
    // {{{ constructor
    /**
     * Constructor
     *
     * @param string $developerId Developer ID
     * @access public
     * @return void
     */
    public function __construct($developerId, $options = array())
    {
        $this->developerId = $developerId;
        $availableOptions = array('useCache', 'cacheOptions', 'responseFormat', 'driver');
        foreach ($options as $key => $value) {
            if (in_array($key, $availableOptions)) {
                $this->$key = $value;
            }
        }
    }
    // }}}
// {{{ setter methods
   /**
     * Choose which driver to use(XML-RPC or REST)
     *
     * @param string $driver
     * @access public
     * @return void
     * @throws Services_YouTube_Exception
     */
    public function setDriver($driver)
    {
        if ($driver == 'xmlrpc' or $driver == 'rest') {
            $this->driver = $driver;
        } else {
            throw new Services_YouTube_Exception('Driver has to be "xmlrpc" or "rest"');
        }
    }

    /**
     * Choose which Response Fomat to use(object or array)
     *
     * @param string $responseFormat
     * @access public
     * @return void
     * @throws Services_YouTube_Exception
     */
    public function setResponseFormat($responseFormat)
    {
        if ($responseFormat == 'object' or $responseFormat == 'array') {
            $this->responseFormat = $responseFormat;
        } else {
            throw new Services_YouTube_Exception('ResponseFormat has to be "object" or "array"');
        }
    }

    /**
     * Choose if this uses Cache_Lite.
     * If this uses Cache_Lite, then set the cacheOptions for Cache_Lite
     *
     * @param mixed $useCache
     * @param array $cacheOptions
     * @access public
     * @return void
     */
    public function setUseCache($useCache = false, $cacheOptions = array())
    {
        $this->useCache = $useCache;
        if ($useCache) {
            $this->cacheOptions = $cacheOptions;
        }
    }
// }}}
    // {{{ users
    /**
     * Retrieves the public parts of a user profile.
     *
     * @param string $user The user to retrieve the profile for. This is the same as the name that shows up on the YouTube website.
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function getProfile($user)
    {
        $parameters = array('dev_id' => $this->developerId,
            'user'   => $user);
        return $this->sendRequest('youtube.users.', 'get_profile', $parameters);
    }
    /**
     * Lists a user's favorite videos.
     *
     * @param string $user The user to retrieve the favorite videos for. This is the same as the name that shows up on the YouTube website
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function listFavoriteVideos($user)
    {
        $parameters = array('dev_id' => $this->developerId,
            'user'   => $user);
        return $this->sendRequest('youtube.users.', 'list_favorite_videos', $parameters);
    }
    /**
     * Lists a user's friends.
     *
     * @param string $user The user to retrieve the favorite videos for. This is the same as the name that shows up on the YouTube website
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function listFriends($user)
    {
        $parameters = array('dev_id' => $this->developerId,
            'user'   => $user);
        return $this->sendRequest('youtube.users.', 'list_friends', $parameters);
    }
    // }}}
    // {{{ videos
    /**
     * Displays the details for a video.
     *
     * @param string $videoId The ID of the video to get details for. This is the ID that's returned by the list
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function getDetails($videoId)
    {
        $parameters = array('dev_id'   => $this->developerId,
            'video_id' => $videoId);
        return $this->sendRequest('youtube.videos.', 'get_details', $parameters);
    }
    /**
     * Lists all videos that have the specified tag.
     *
     * @param string $tag the tag to search for
     * @param string $page the "page" of results you want to retrieve (e.g. 1, 2, 3) (default 1)
     * @param string $perPage the number of results you want to retrieve per page (default 20, maximum 100)
     *
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function listByTag($tag, $page = 1, $perPage = 20)
    {
        if ($perPage > self::VIDEO_PER_PAGE_MAX) {
            throw new Services_YouTube_Exception('The Maximum of the perPage is ' . self::VIDEO_PER_PAGE_MAX);
        }
        $parameters = array('dev_id' => $this->developerId,
            'tag'    => $tag,
            'page' => $page,
            'per_page' => $perPage);
        return $this->sendRequest('youtube.videos.', 'list_by_tag', $parameters);
    }

    /**
     * Lists all videos that were uploaded by the specified user
     *
     * @param string $user user whose videos you want to list
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function listByUser($user)
    {
        $parameters = array('dev_id' => $this->developerId,
            'user'   => $user);
        return $this->sendRequest('youtube.videos.', 'list_by_user', $parameters);
    }

    /**
     * Lists the most recent 25 videos that have been featured on the front page of the YouTube site.
     *
     * @access public
     * @return array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    public function listFeatured()
    {
        $parameters = array('dev_id' => $this->developerId);
        return $this->sendRequest('youtube.videos.', 'list_featured', $parameters);
    }
    // }}}
    // {{{ protected
    /**
     *  Send Request either rest or xmlrpc approach, and return simplexml_element of the response.
     *  When $this->usesCaches is true, use Cache_Lite the response xml.
     *  If $this->responseFormat is "array", return array, instead simplexml_element.
     *
     * @param string $prefix
     * @param string $method
     * @param array $parameters
     * @access protected
     * @return SimpleXMLObject or Array on success, error object on failure
     * @throws Services_YouTube_Exception
     */
    protected function sendRequest($prefix, $method, $parameters)
    {
        // Use Cache_Lite
        if ($this->useCache) {
            require_once 'Cache/Lite.php';
            $cacheID = md5($prefix . $method . serialize($parameters));
            $cache = new Cache_Lite($this->cacheOptions);

            if ($response = $cache->get($cacheID)) {
                return $this->parseResponse($response);
            }
        }

        if ($this->driver == 'rest') {
            $response = $this->useRest($prefix . $method, $parameters);
        } else if ($this->driver == 'xmlrpc') {
            $response = $this->useXMLRPC($prefix, $method, $parameters);
        } else {
            throw new Services_YouTube_Exception('Driver has to be "xmlrpc" or "rest"');
        }
        $data = $this->parseResponse($response);

        // Use Cache_Lite
        if ($this->useCache and isset($cache)) {
            if (!$cache->save($response, $cacheID)) {
                throw new Services_YouTube_Exception('Can not write cache');
            }
        }
        return $data;
    }

    /**
     * Use REST approach.
     *
     * @param string $method
     * @param array $parameters
     * @access protected
     * @return string
     * @throws Services_YouTube_Exception
     */
    protected function useRest($method, $parameters)
    {
        if (!function_exists('curl_init')) {
            throw new Services_YouTube_Exception('cannot use curl exntensions');
        }
        if (!$ch = curl_init()) {
            throw new Services_YouTube_Exception('Unable to setup curl');
        }
        $url = 'http://' . self::URL . self::REST_PATH . '?method=' . $method;
        foreach ($parameters as $key => $val) {
            $url .= '&' . $key . '=' . urlencode($val);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: Service_YouTube: ' . self::VERSION);
        $result = curl_exec($ch);
        if (($errno = curl_errno($ch)) != 0) {
            throw new Services_YouTube_Exception('Curl returned non-null errno ' . $errno .':' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    /**
     * Use XML-RPC approach.
     *
     * @param string $prefix
     * @param string $method
     * @param array $parameters
     * @access protected
     * @return string
     * @throws Services_YouTube_Exception
     */
    protected function useXMLRPC($prefix, $method, $parameters)
    {
        require_once 'XML/RPC2/Client.php';

        $options = array('prefix' => $prefix);
        try {
            $client = XML_RPC2_Client::create('http://' . self::URL . self::XMLRPC_PATH, $options);
            $result = $client->$method($parameters);
        } catch (XML_RPC2_FaultException $e) {
            throw new Services_YouTube_Exception('XML_RPC Failed :' . $e->getMessage());
        } catch (Exception $e) {
            throw new Services_YouTube_Exception($e->getMessage());
        }
        return $result;
    }

    /**
     * parseResponse
     *
     * @param string $response
     * @access protected
     * @return SimpleXMLElement or array of the response data.
     * @throws Services_YouTube_Exception
     */
    protected function parseResponse($response)
    {
        set_error_handler(array('Services_YouTube_Exception', 'errorHandlerCallback'), E_ALL);
        try {
            if (!$data = simplexml_load_string($response)) {
                throw new Services_YouTube_Exception('Parsing Failed. Response string is invalid');
            }
            if ($this->responseFormat == 'array') {
                $data = $this->forArray($data);
            }
            restore_error_handler();
        } catch (Services_YouTube_Exception $e) {
            restore_error_handler();
            throw $e;
        }
        return $data;
    }

    /**
     * Parse all SimpleXMLElement to array
     *
     * @param mixed $object SimpleXMLElement or array
     * @access protected
     * @return array
     */
    protected function forArray($object)
    {
        $return = array();

        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $return[$key] = $this->forArray($value);
            }
        } else {
            $vars = get_object_vars($object);
            if (is_array($vars)) {
                foreach ($vars as $key => $value) {
                    $return[$key] = ($key && !$value) ? null : $this->forArray($value);
                }
            } else {
                return $object;
            }
        }
        return $return;
    }
    // }}}
}

?>
