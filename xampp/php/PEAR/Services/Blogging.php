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
* @version  CVS: $Id: Blogging.php,v 1.5 2008/11/29 13:15:46 cweiske Exp $
* @link     http://pear.php.net/package/Services_Blogging
*/

require_once 'Services/Blogging/Exception.php';

/**
* Generic package for several Blogging APIs.
*
* Create a new instance via
* Services_Blogging::factory($driver, $username, $password, $server, $path),
* or more easy via
* Services_Blogging::discoverDriver($url, $username, $password) .
*
* Note that some Blogging APIs allow multiple blogs with one
* account. These drivers implement Services_Blogging_MultipleBlogsInterface
* - you need to call setBlogId($id) before you can use the driver in that case.
*
* @category Services
* @package  Services_Blogging
* @author   Christian Weiske <cweiske@php.net>
* @author   Anant Narayanan <anant@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @link     http://pear.php.net/package/Services_Blogging
*
* @todo
* Missing drivers
* - MovableType
* - Conversant
* - Manila
* - MetaWiki
* - Antville
*/
class Services_Blogging
{
    /**
    * Exception code for a problem with the driver:
    * - driver does not exist
    * - driver does not extend base driver class
    */
    const ERROR_DRIVER = 101;

    /**
    * Exception code when the bloggin system does not support autodiscovery
    */
    const ERROR_BLOGHASNTAUTODISCOVERY = 102;

    /**
    * Exception code when autodiscovery succeeded, but not matching driver
    * could be found
    */
    const ERROR_NOSUPPORTEDDRIVER = 103;

    /**
    * Exception code when something (method, property) is not supported.
    */
    const ERROR_NOTSUPPORTED = 104;

    /**
     * USER_AGENT to send along during requests.
     */
    const USER_AGENT = 'PHP/Services_Blogging-0.2.3';


    /**
    * The factory function that instantiates the appropriate class and returns
    * the object, so that further methods may be executed. This function serves
    * as the single entry point for the class.
    *
    * @param string $driver   The driver name, currently either 'Blogger',
    *                         'MetaWeblog' or 'LiveJournal'.
    * @param string $username The username of the blog account to connect to.
    * @param string $password The password of the blog account to connect to.
    * @param string $server   The URI of the blog's server with protocol.
    * @param string $path     The location of the XML-RPC server script.
    *
    * @return Services_Blogging_Driver Blogging driver instance
    *
    * @throws Services_Blogging_Exception If authentication fails
    * @throws Services_Blogging_Exception If the driver does not exist.
    * @throws Services_Blogging_Exception If the driver does not extend the
    *                                     base driver class
    *                                     Services_Blogging_Driver
    */
    public static function factory($driver, $username, $password, $server, $path)
    {
        //security measurement
        $driver = preg_replace('/[^a-z0-9_]/i', '', $driver);

        include_once 'Services/Blogging/Driver/' . $driver . '.php';
        $strClass = 'Services_Blogging_Driver_' . $driver;
        if (!class_exists($strClass)) {
            throw new Services_Blogging_Exception(
                'Invalid driver "' . $driver . '" specified!', self::ERROR_DRIVER
            );
        }
        if (!is_subclass_of($strClass, 'Services_Blogging_Driver')) {
            throw new Services_Blogging_Exception(
                'Driver class ' . $strClass . ' is not a child class'
                . ' of Services_Blogging_Driver', self::ERROR_DRIVER
            );
        }

        //handle slighly wrong user input: no path but slash in server
        if ($path == null && substr($server, -1) == '/') {
            $server = substr($server, 0, -1);
        }

        $class = new $strClass($username, $password, $server, $path);
        return $class;
    }//public static function factory($driver, $username, $password, $server, $path)



    /**
    * Autodiscover the driver settings from the given blog URL
    * and create a driver instance.
    *
    * @param string $url      Blog URL
    * @param string $username Username for the blog account
    * @param string $password Password for the blog account
    *
    * @return Services_Blogging_Driver The driver object if all goes ok
    *
    * @throws Services_Blogging_Exception If an error occured
    */
    public static function discoverDriver($url, $username, $password)
    {
        $settings = self::discoverSettings($url);
        if ($settings === false) {
            throw new Services_Blogging_Exception(
                'Autodiscovery of settings not supported by the blog',
                self::ERROR_BLOGHASNTAUTODISCOVERY
            );
        }
        $driver = self::getBestAvailableDriver($settings);
        if ($driver === false) {
            throw new Services_Blogging_Exception(
                'None of the supported drivers available',
                self::ERROR_NOSUPPORTEDDRIVER
            );
        }

        return self::factory(
            $driver,
            $username,
            $password,
            $settings['apis'][$driver]['server'],
            $settings['apis'][$driver]['path']
        );
    }//public static function discoverDriver($url, $username, $password)



    /**
    * Tries to auto-discover the driver settings for the blog
    * at the given URL.
    * Internally, an RSD page is tried to load and read.
    *
    * @param string $url Url of the blog
    *
    * @return mixed FALSE if nothing found, OR array of settings:
    *                  - engineName
    *                  - engineLink
    *                  - homePageLink
    *                  - apis => array (key is name
    *                      - name
    *                      - preferred
    *                      - apiLink (url)
    *                      - server (for factory())
    *                      - path   (for factory())
    *
    *  @link http://archipelago.phrasewise.com/display?page=oldsite/1330.html
    */
    public static function discoverSettings($url)
    {
        $content = file_get_contents($url);
        if ($content === false) {
            return false;
        }

        //search for a line like this:
        //<link rel="EditURI" type="application/rsd+xml" title="RSD"
        // href="http://blog.bogo/xmlrpc.php?rsd" />
        if (!preg_match_all('|<link\\s+rel="EditURI".+href="(.+?)"|',
                $content, $matches)
        ) {
            return false;
        }

        $rsdUrl = reset($matches[1]);
        $root   = simplexml_load_string(file_get_contents($rsdUrl));
        if ($root === false) {
            return false;
        }

        $apis = array();
        foreach ($root->service->apis->api as $api) {
            $ap         = array();
            $ap['name'] = (string)$api['name'];
            if ($ap['name'] == 'Movable Type') {
                $ap['name'] = 'MovableType';
            }
            $ap['preferred'] = $api['preferred'] == 1 || $api['preferred'] == 'true';
            $ap['apiLink']   = (string)$api['apiLink'];

            //try to get server and path
            $dslashpos = strpos($ap['apiLink'], '//');
            if ($dslashpos === false) {
                $nBegin = 0;
            } else {
                $nBegin = $dslashpos + 2;
            }
            $slashpos     = strpos($ap['apiLink'], '/', $nBegin);
            $ap['server'] = substr($ap['apiLink'], 0, $slashpos);
            $ap['path']   = substr($ap['apiLink'], $slashpos);

            $apis[$ap['name']] = $ap;
        }

        $data = array(
            'engineName'   => (string)$root->service->engineName,
            'engineLink'   => (string)$root->service->engineLink,
            'homePageLink' => (string)$root->service->homePageLink,
            'apis'         => $apis
        );
        return $data;
    }//public static function discoverSettings($url)



    /**
    * Tries to return the best available driver for the given
    * settings array. The settings array is returned by
    * Services_Blogging::discoverSettings()
    *
    * @param array $arSettings Settings array
    *
    * @return string The driver to use, false if none found
    */
    public static function getBestAvailableDriver($arSettings)
    {
        if (isset($arSettings['apis'])) {
            $arSettings = $arSettings['apis'];
        }
        //find preferred one
        $driver = null;
        foreach ($arSettings as $id => $api) {
            if ($api['preferred'] === true) {
                if (self::driverExists($api['name'])) {
                    return $api['name'];
                }
                unset($arSettings[$id]);
            }
        }
        foreach ($arSettings as $id => $api) {
            if (self::driverExists($api['name'])) {
                return $api['name'];
            }
        }

        return false;
    }//public static function getBestAvailableDriver($arSettings)



    /**
    * Tries to include the driver file and checks if
    * the driver class exists.
    *
    * @param string $driver Driver to check
    *
    * @return boolean If the driver exists
    */
    protected static function driverExists($driver)
    {
        @include_once 'Services/Blogging/Driver/' . $driver . '.php';
        return class_exists('Services_Blogging_Driver_' . $driver);
    }//protected static function driverExists($driver)

}//class Services_Blogging
?>