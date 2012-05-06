<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Akismet is a package to use Akismet spam-filtering from PHP
 *
 * This package provides an object-oriented interface to the Akismet REST
 * API. Akismet is used to detect and to filter spam comments posted on
 * weblogs. Though the use of Akismet is not specific to Wordpress, you will
 * need a Wordpress API key from {@link http://wordpress.com} to use this
 * package.
 *
 * Akismet is free for personal use and a license may be purchased for
 * commercial or high-volume applications.
 *
 * This package is derived from the miPHP Akismet class written by Bret Kuhns
 * for use in PHP 4. This package requires PHP 5.2.1.
 *
 * Example usage:
 * <code>
 *
 * /**
 *  * Handling user-posted comments
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     if ($akismet->isSpam($comment)) {
 *         // rather than simply ignoring the spam comment, it is recommended
 *         // to save the comment and mark it as spam in case the comment is a
 *         // false positive.
 *     } else {
 *         // save comment as normal comment
 *     }
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as known spam
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     $akismet->submitSpam($comment);
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as a false positive
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     $akismet->submitFalsePositive($comment);
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * </code>
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
 * @version   CVS: $Id: Akismet.php,v 1.6 2008/04/16 03:10:41 gauthierm Exp $
 * @link      http://pear.php.net/package/Services_Akismet
 * @link      http://akismet.com/
 * @link      http://akismet.com/development/api/
 * @link      http://www.miphp.net/blog/view/php4_akismet_class
 */

/**
 * Comment class definition.
 */
require_once 'Services/Akismet/Comment.php';

/**
 * Simple HTTP client for accessing the Akismet API.
 */
require_once 'Services/Akismet/HttpClient.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet/InvalidApiKeyException.php';

// {{{ class Services_Akismet

/**
 * Class to use Akismet API from PHP
 *
 * Example usage:
 * <code>
 *
 * /**
 *  * Handling user-posted comments
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     if ($akismet->isSpam($comment)) {
 *         // rather than simply ignoring the spam comment, it is recommended
 *         // to save the comment and mark it as spam in case the comment is a
 *         // false positive.
 *     } else {
 *         // save comment as normal comment
 *     }
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as known spam
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     $akismet->submitSpam($comment);
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as a false positive
 *  {@*}
 *
 * $comment = new Services_Akismet_Comment();
 * $comment->setAuthor('Test Author');
 * $comment->setAuthorEmail('test@example.com');
 * $comment->setAuthorUri('http://example.com/');
 * $comment->setContent('Hello, World!');
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet('http://blog.example.com/', $apiKey);
 *     $akismet->submitFalsePositive($comment);
 * } catch (Services_Akismet_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet_CommunicationException $comException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $comException->getMessage();
 * } catch (Services_Akismet_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * </code>
 *
 * @category  Services
 * @package   Services_Akismet
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet
 */
class Services_Akismet
{
    // {{{ private properties

    /**
     * The port to use to connect to the Akismet API server
     *
     * Defaults to 80.
     *
     * @var integer
     */
    private $_apiPort    = 80;

    /**
     * The Akismet API server name
     *
     * Defaults to 'rest.akismet.com'.
     *
     * @var string
     */
    private $_apiServer  = 'rest.akismet.com';

    /**
     * The Akismet API version to use
     *
     * Defaults to '1.1'.
     *
     * @var string
     */
    private $_apiVersion = '1.1';

    /**
     * The URI of the webblog for which Akismet services will be used
     *
     * @var string
     *
     * @see Services_Akismet::__construct()
     */
    private $_blogUri = '';

    /**
     * The Wordpress API key to use to access Akismet services
     *
     * @var string
     *
     * @see Services_Akismet::__construct()
     */
    private $_apiKey  = '';


    /**
     * The HTTP client used to communicate with the Akismet REST API server
     *
     * @var Services_Akismet_HttpClient
     *
     * @see Services_Akismet::setHttpClientImplementation()
     */
    private $_httpClient = null;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new Akismet object
     *
     * @param string $blogUri                  the URI of the webblog homepage.
     * @param string $apiKey                   the Wordpress API key to use for
     *                                         Akismet services.
     * @param string $httpClientImplementation optional. The name of the HTTP
     *                                         client implementation to use.
     *                                         This must be one of the
     *                                         implementations specified by
     *                                         {@link Services_Akismet_HttpClient}.
     *                                         If not specified, defaults to
     *                                         'sockets'.
     *
     * @throws Services_Akismet_InvalidApiKeyException if the provided
     *         Wordpress API key is not valid.
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws PEAR_Exception if the specified HTTP client implementation may
     *         not be used with this PHP installation or if the specified HTTP
     *         client implementation does not exist.
     */
    public function __construct($blogUri, $apiKey,
        $httpClientImplementation = 'sockets')
    {
        $this->_blogUri = $blogUri;
        $this->_apiKey  = $apiKey;

        // build http client
        $this->setHttpClientImplementation($httpClientImplementation);

        // make sure the API key is valid
        if (!$this->_isApiKeyValid($this->_apiKey)) {
            throw new Services_Akismet_InvalidApiKeyException('The specified ' .
                'Wordpress API key is not valid. Key used was: "' .
                $this->_apiKey . '".', 0, $this->_apiKey);
        }
    }

    // }}}
    // {{{ isSpam()

    /**
     * Checks whether or not a comment is spam
     *
     * @param Services_Akismet_Comment $comment the comment to check.
     *
     * @return boolean true if the comment is spam and false if it is not.
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet_InvalidCommentException if the specified comment
     *         is missing required fields.
     */
    public function isSpam(Services_Akismet_Comment $comment)
    {
        $postData = $comment->getPostData();
        $postData = 'blog=' . urlencode($this->_blogUri) . '&' . $postData;
        $response = $this->_request('comment-check', $postData);
        return ($response == 'true');
    }

    // }}}
    // {{{ submitSpam()

    /**
     * Submits a comment as an unchecked spam to the Akismet server
     *
     * Use this method to submit comments that are spam but are not detected
     * by Akismet.
     *
     * @param Services_Akismet_Comment $comment the comment to submit as spam.
     *
     * @return void
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet_InvalidCommentException if the specified comment
     *         is missing required fields.
     */
    public function submitSpam(Services_Akismet_Comment $comment)
    {
        $postData = $comment->getPostData();
        $postData = 'blog=' . urlencode($this->_blogUri) . '&' . $postData;
        $this->_request('submit-spam', $postData);
    }

    // }}}
    // {{{ submitFalsePositive()

    /**
     * Submits a false-positive comment to the Akismet server
     *
     * Use this method to submit comments that are detected as spam but are not
     * actually spam.
     *
     * @param Services_Akismet_Comment $comment the comment that is
     *                                          <em>not</em> spam.
     *
     * @return void
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet_InvalidCommentException if the specified comment
     *         is missing required fields.
     */
    public function submitFalsePositive(Services_Akismet_Comment $comment)
    {
        $postData = $comment->getPostData();
        $postData = 'blog=' . urlencode($this->_blogUri) . '&' . $postData;
        $this->_request('submit-ham', $postData);
    }

    // }}}
    // {{{ setHttpClientImplementation()

    /**
     * Sets the HTTP client implementation to use for this Akismet object
     *
     * Available implementations are:
     * - sockets
     * - streams
     * - curl
     *
     * @param string $implementation the name of the HTTP client implementation
     *                               to use. This must be one of the
     *                               implementations specified by
     *                               {@link Services_Akismet_HttpClient}.
     *
     * @return void
     *
     * @throws PEAR_Exception if the specified HTTP client implementation may
     *         not be used with this PHP installation or if the specified HTTP
     *         client implementation does not exist.
     *
     * @see Services_Akismet_HttpClient
     */
    public function setHttpClientImplementation($implementation)
    {
        $servicesAkismetName    = 'Services_Akismet';
        $servicesAkismetVersion = '1.0.1';

        $userAgent = sprintf('%s/%s | Akismet/%s',
            $servicesAkismetName,
            $servicesAkismetVersion,
            $this->_apiVersion);

        $this->_httpClient =
            Services_Akismet_HttpClient::factory($this->_apiServer,
                $this->_apiPort, $userAgent, $implementation);
    }

    // }}}
    // {{{ _isApiKeyValid()

    /**
     * Checks with the Akismet server to determine if a Wordpress API key is
     * valid
     *
     * @param string $key the Wordpress API key to check.
     *
     * @return boolean true if the key is valid and false if it is not valid.
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     */
    private function _isApiKeyValid($key)
    {
        $postData = sprintf('key=%s&blog=%s',
            urlencode($key),
            urlencode($this->_blogUri));

        $response = $this->_request('verify-key', $postData);
        return ($response == 'valid');
    }

    // }}}
    // {{{ _request()

    /**
     * Calls a method on the Akismet API server using a HTTP POST request
     *
     * @param string $methodName the name of the Akismet method to call.
     * @param string $content    the post content of the request. This contains
     *                           Akismet method parameters.
     *
     * @return string the HTTP response content.
     *
     * @throws Services_Akismet_CommunicationException if there is an error
     *         communicating with the Akismet API server.
     */
    private function _request($methodName, $content)
    {
        $path = sprintf('/%s/%s', $this->_apiVersion, $methodName);
        $response = $this->_httpClient->post($path, $content, $this->_apiKey);
        return $response;
    }

    // }}}
}

// }}}

?>
