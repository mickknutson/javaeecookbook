<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | Services_Google                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Jon Wood                                          |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jon Wood <jon@jellybob.co.uk>                               |
// +----------------------------------------------------------------------+
//

/**
 * A PHP implementation of the Google Web API (http://www.google.com/apis/)
 *
 * To use this package you will need to register for a search key.
 *
 * @author      Jon Wood <jon@jellybob.co.uk>
 * @package     Services_Google
 * @category    Services
 * @copyright   Jon Wood, 2004
 */
class Services_Google implements Iterator
{
    /**
     * The key to use for queries.
     *
     * @var     string
     * @access  public
     */
    public $key;

    /**
     * The object being used for queries.
     *
     * @var     SoapClient
     * @access  private
     */
    private $_soapClient;

    /**
     * The last query to be made.
     *
     * @var     string
     * @access  private
     */
    private $_lastQuery = "";

    /**
     * The current index which has been reached.
     *
     * @var     int
     * @access  private
     */
    private $_index = 0;

    /**
     * The last resultset retrieved.
     *
     * @var     Object
     * @access  private
     */
    private $_result = null;

    /**
     * An array of options to be applied to queries.
     *
     * @var     array
     * @access  public
     */
    public $queryOptions = array(
                                    "start"         => 0,
                                    "maxResults"    => 10,
                                    "limit"         => false,
                                    "filter"        => true,
                                    "restricts"     => "",
                                    "safeSearch"    => true,
                                    "language"      => ""
                                );

    /**
     * Constructor
     *
     * @param   string  $key    The web services key provided by Google.
     * @return  null
     * @access  public
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->_soapClient = new SoapClient('http://api.google.com/GoogleSearch.wsdl');
    }

    /**
     * Set Query options
     *
     * @param   array   $options   An array of options
     * @access  public
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            foreach($options as $key => $value) {
                if (isset($this->queryOptions[$key])) {
                    $this->queryOptions[$key] = $value;
                }
            }
        }
    }

    /**
     * Setup up a search to be run.
     *
     * Once you've run this method, you need to call fetch() to get results.
     * You can set search options with the queryOptions variable.
     *
     * @param   string  $query  The query string to use.
     * @return  null
     * @see     Services_Google::fetch()
     * @see     Services_Google::$queryOptions
     * @access  public
     */
    public function search($query)
    {
        $this->_lastQuery = $query;
        $this->_index = $this->queryOptions["start"];
        $this->_result = null;
    }

    /**
     * Fetch results from a search.
     *
     * This method will return a GoogleSearchResult object, or false if no
     * results were found, or the limit has been reached.
     *
     * @return  GoogleSearchResult|false
     * @see     http://api.google.com/GoogleSearch.wsdl
     * @see     Services_Google::search()
     * @access  public
     */
    public function fetch()
    {
        if (isset($this->queryOptions["limit"])) {
            if ($this->queryOptions["limit"] <= $this->_index) {
                return false;
            }
        }

        if (is_null($this->_result)) {
            $this->runQuery();
        }

        if ($this->_index == $this->_result->endIndex - 1) {
            $this->runQuery();
        }

        if (count($this->_result->resultElements)) {
            $this->_index++;
            return $this->_result->resultElements[$this->_index - $this->_result->startIndex];
        } else {
            return false;
        }
    }

    /**
     * Returns the number of results for the current query.
     *
     * @return  int
     * @access  public
     */
    public function getResultsCount()
    {
        if (is_null($this->_result) && !empty($this->_lastQuery)) {
            $this->runQuery();
        }

        return $this->_result->estimatedTotalResultsCount;
    }

    /**
     * Returns search time
     *
     * @return  float
     * @access  public
     */
    public function getSearchTime()
    {
        if (is_null($this->_result) && !empty($this->_lastQuery)) {
            $this->runQuery();
        }

        return $this->_result->searchTime;
    }

    /**
     * Does a spell check using Google's spell checking engine.
     *
     * @param   string $phrase The string to spell check.
     * @return  string A suggestion of how the phrase should be spelt.
     * @access  public
     */
    public function spellingSuggestion($phrase)
    {
        return $this->_soapClient->doSpellingSuggestion($this->key, $phrase);
    }

    /**
     * Gets a cached page from Google's cache.
     *
     * @param   string  $url    The page to get.
     * @return  string  The cached page.
     * @access  public
     */
    public function getCachedPage($url)
    {
        $result = $this->_soapClient->doGetCachedPage($this->key, $url);
        return base64_decode($result);
    }

    /**
     * Runs a query when neccesary
     *
     * @return  null
     * @access  private
     */
    private function runQuery()
    {
        $this->_result = $this->_soapClient->doGoogleSearch(
                                            $this->key,
                                            $this->_lastQuery,
                                            $this->_index,
                                            $this->queryOptions["maxResults"],
                                            $this->queryOptions["filter"],
                                            $this->queryOptions["restricts"],
                                            $this->queryOptions["safeSearch"],
                                            $this->queryOptions["language"],
                                            "",
                                            ""
                                        );
    }

    public function valid()
    {
        if (isset($this->queryOptions["limit"])) {
            if ($this->queryOptions["limit"] <= $this->_index) {
                return false;
            }
        }

        if (is_null($this->_result)) {
            $this->runQuery();
        }

        if ($this->_index == $this->_result->endIndex - 1) {
            $this->runQuery();
        }

        if ($this->_index >= $this->_result->estimatedTotalResultsCount - 1) {
            return false;
        }

        return (bool)count($this->_result->resultElements);
    }

    public function current()
    {
        if ($this->_result->startIndex < 10) {
            return $this->_result->resultElements[$this->_index];
        } else {
            return $this->_result->resultElements[$this->_index - ($this->_result->startIndex - 1)];
        }
    }

    public function next()
    {
        $this->_index++;
    }

    public function rewind()
    {
        $this->_index = 0;
    }

    public function key()
    {
        return $this->_index;
    }
}
?>
