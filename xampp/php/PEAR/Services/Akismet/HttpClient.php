<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Abstract class definition for HTTP client for Akismet REST API
 *
 * This abstract class has a factory method to create an instance using a
 * particular implementation. For example:
 *
 * <code>
 * // creates a streams-based http client for use with the Akismet package
 * $client = Services_Akismet_HttpClient::factory('rest.akismet.com', 80,
 *     'Services_Akismet', 'streams');
 * </code>
 *
 * The available implementations are:
 * - sockets (used by default)
 * - streams
 * - curl
 *
 * Services_Akismet is a package to use Akismet spam-filtering from PHP
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 Bret Kuhns, silverorange
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Akismet
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id: HttpClient.php,v 1.4 2008/04/16 03:10:41 gauthierm Exp $
 * @link      http://pear.php.net/package/Services_Akismet
 * @link      http://akismet.com/development/api/
 */

/**
 * Base PEAR exception class.
 */
require_once 'PEAR/Exception.php';

// {{{ class Services_Akismet_HttpClient

/**
 * Abstract simple HTTP client for accessing the Akismet REST API
 *
 * This class contains a factory method for creating instances using a
 * particular implementation. For example:
 *
 * <code>
 * <?php
 * // creates a streams-based http client for use with the Akismet package
 * $client = Services_Akismet_HttpClient::factory('streams',
 *     'rest.akismet.com', 80, 'Services_Akismet');
 * ?>
 * </code>
 *
 * The available implementations are:
 * - sockets (default)
 * - streams
 * - curl
 *
 * This HTTP client only supports the HTTP POST method since that is all that
 * is needed for the Akismet API.
 *
 * Example usage:
 *
 * <code>
 * // creates a streams-based http client for use with the Akismet package
 * $client = Services_Akismet_HttpClient::factory('rest.akismet.com', 80,
 *     'Services_Akismet', 'streams');
 * </code>
 *
 * @category  Services
 * @package   Services_Akismet
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet
 * @link      http://akismet.com/development/api/
 */
abstract class Services_Akismet_HttpClient
{
    // {{{ factory()

    /**
     * Factory method to instantiate a HTTP client implementation
     *
     * @param string  $host           the Akismet API server host name.
     * @param integer $port           the TCP/IP connection port of the HTTP
     *                                client.
     * @param string  $userAgent      the HTTP user agent of the HTTP client.
     * @param string  $implementation optional. The name of the implementation
     *                                to instantiate. Must be one of 'sockets',
     *                                'streams' or 'curl'. If not specified,
     *                                defaults to 'sockets'.
     *
     * @return Services_Akismet_HttpClient the instantiated HTTP client
     *         implementation.
     *
     * @throws PEAR_Exception if the implementation is not supported by the
     *         current PHP installation or if the provided implementation does
     *         not exist.
     */
    public static function factory($host, $port, $userAgent,
        $implementation = 'sockets')
    {
        $drivers = array(
            'sockets' => 'Socket',
            'streams' => 'Stream',
            'curl'    => 'Curl'
        );

        if (!array_key_exists($implementation, $drivers)) {
            throw new PEAR_Exception('Services_Akismet_HttpClient ' .
                'implementation "' . $implementation. '" does not exist.');
        }

        $filename = 'Services/Akismet/HttpClient/' .
            $drivers[$implementation] . '.php';

        include_once $filename;

        $className = 'Services_Akismet_HttpClient_' .
            $drivers[$implementation];

        $object = new $className($host, $port, $userAgent);

        return $object;
    }

    // }}}
    // {{{ post()

    /**
     * Makes a HTTP POST request on the Akismet API server
     *
     * @param string $path    the resource to post to.
     * @param string $content the data to post.
     * @param string $apiKey  optional. The Wordpress API key to use for the
     *                        request. If not specified, no API key information
     *                        is included in the request. This is used for key
     *                        validation.
     *
     * @return string the content of the HTTP response from the Akismet API
     *                server.
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     */
    abstract public function post($path, $content, $apiKey = '');

    // }}}
    // {{{ __construct()

    /**
     * Creates a new HTTP client for accessing the Akismet REST API
     *
     * Instances of this HTTP client must be instantiated using the
     * {@link Services_Akismet_HttpClient::factory()} method.
     *
     * @param string  $host      the Akismet API server host name.
     * @param integer $port      the TCP/IP connection port of this HTTP
     *                           client.
     * @param string  $userAgent the HTTP user agent of this HTTP client.
     *
     * @throws PEAR_Exception if the implementation is not supported by the
     *         current PHP installation.
     */
    abstract protected function __construct($host, $port, $userAgent);

    // }}}
}

// }}}

?>
