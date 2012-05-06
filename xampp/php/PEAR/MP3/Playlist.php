<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * Class to generate playlists in m3u and other formats including smil,
 * sqlite, XHTML, RSS and raw XML
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * The PHP License, version 3.0
 *
 * Copyright (c) 2004-2005 David Costa
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category    File Formats
 * @package     MP3_Playlist
 * @author      David Costa <gurugeek@php.net>
 * @author      Ashley Hewson <morbidness@gmail.com>
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   Copyright (c) 2004-2005 David Costa
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     CVS: $Id: Playlist.php,v 1.2 2006/09/03 06:30:12 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load PEAR_Exception for throw on error.
 */
require_once 'PEAR/Exception.php';

/**
 * Require MP3 ID for extensive support of tags within a playlist
 */
require_once 'MP3/Id.php';

/**
 * Load Net_URL for fix the given URL.
 */
require_once 'Net/URL.php';

// }}}
// {{{ Class: MP3_Playlist

/**
 * Class MP3_Playlist
 *
 * A playlist can be used for both local use and remote streaming.
 *
 * The m3u playlist format is supported  by Winamp, XMMS, Noatunes and several
 * other players.
 *
 * The Smil playlist format is supported by Real Media Player, Quicktime and
 * others XHTML allows you to display a fully formatted page or just the tables
 * to be included in your own template.
 *
 * RSS allows you to syndicate the playlist on the fly. The Sqlite function
 * allows you to backup the list in an existing sqlite database or to create a
 * database and the table on the fly.
 *
 * @category    File Formats
 * @package     MP3_Playlist
 * @author      David Costa <gurugeek@php.net>
 * @author      Ashley Hewson <morbidness@gmail.com>
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   Copyright (c) 2004-2005 David Costa
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     Release: 0.5.1alpha1
 */
class MP3_Playlist
{
    // {{{ Object Constants

    /**
     * Error handling related Constants
     * Verbose error output in each of the cases
     */
    const E_CANNOT_OPENDIR      = 'Unable to open the directory';
    const E_CANNOT_SAVEFILE     = 'Unable to save file';
    const E_CANNOT_OPENDB       = 'Unable to open the database';
    const E_CANNOT_CREATETABLE  = 'Unable to create the playlist table';
    const E_CANNOT_INSERT       = 'Unable to insert into the playlist table';
    const E_CANNOT_SENDHEADER   = 'Unable to send HTTP header';
    const E_INVALID_MERGE       = 'Unable to merge';
    const E_INVALID_PLAYLIST    = 'Invalid playlist';
    const E_UNKNOWN_TYPE        = 'Unknown format type';
    const E_UNSUPPORTED_TYPE    = 'Unsupported format type';
    const E_NOT_SAVEABLE        = 'Playlist format is not saveable';
    const E_NOT_SENDABLE        = 'Playlist format is not sendable';
    const E_REQUIRED_PARAM      = 'Required parameter';

    /**
     * Output type made by MP3_Playlist.
     */
    const TYPE_M3U      = 'M3U';
    const TYPE_SMIL     = 'SMIL';
    const TYPE_XML      = 'XML';
    const TYPE_XHTML    = 'XHTML';
    const TYPE_RSS      = 'RSS';
    const TYPE_SQLITE   = 'SQLITE';

    // }}}
    // {{{ Static Properties

    public static $drivers = array(
        'm3u'   => array(
            'file'  => 'M3U.php',
            'class' => 'MP3_Playlist_M3U'
        ),
        'smil'  => array(
            'file'  => 'SMIL.php',
            'class' => 'MP3_Playlist_SMIL'
        ),
        'xml'   => array(
            'file'  => 'XML.php',
            'class' => 'MP3_Playlist_XML'
        ),
        'xhtml' => array(
            'file'  => 'XHTML.php',
            'class' => 'MP3_Playlist_XHTML'
        ),
        'rss'   => array(
            'file'  => 'RSS.php',
            'class' => 'MP3_Playlist_RSS'
        ),
        'sqlite'    => array(
            'file'  => 'SQLite.php',
            'class' => 'MP3_Playlist_SQLite'
        )
    );

    // {{{ Object Variables

    /**
     * Debug value
     * @var string
     */
    public $debug = false;

    /**
     * Directory to Parse
     * @var string
     */
    private $parseDirectory;

    /**
     * Output directory, where the playlist is saved
     * @var string
     */
    private $outputDirectory;

    /**
     * Url to append on the playlist
     * @var string
     */
    private $baseUrl;

    /**
     * List of songs in an array, formatted after parsing
     * @var array
     */
    private $list = array();

    /**
     * MP3_ID Object
     * @var string
     */
    private $mp3;

    /**
     * List of files including append directory, location and ID3 tags for XML
     * and other manipulation
     * @var array
     */
    private $merged = array();

    /**
     * private variable to check if the method Mergelist is called
     * @var string
     */
    private $isMerged = false;

    /**
     * Driver object instance.
     * @var object
     */
    private $playlist = null;

    // }}}
    // {{{ Constructor

    /**
     * Instantiate a new MP3_Playlist object.
     *
     * Expects a reading directory, the output directory where the playlist will
     * be saved and the directory or URL to be used within the playlist.
     *
     * @param   string $dir     The directory to scan
     * @param   string $outdir  The directory where to save the playlist file
     * @param   string $baseurl The base url to append on the playlist file
     * @param   bool   $debug   (optional) Whether print debug message or not, default FALSE
     *
     * @return  TRUE|PEAR_Error
     * @see     MP3_Playlist::fixPath()
     */
    public function __construct ($dir, $outdir, $baseurl, $debug = false)
    {
        // Taking the values from the constructor and assigning it to the
        // private variables
        $this->parseDirectory = self::fixPath($dir);
        $this->outputDirectory = self::fixPath($outdir);

        // Fix the URL if needed.
        if (substr($baseurl, -1) != '/') {
            $baseurl .= '/';
        }

        $url = new Net_URL($baseurl);
        if (!empty($url->path)) {
            $dirs = explode('/', $url->path);
            foreach ($dirs as $key => $value) {
                if (!empty($value)) {
                    $dirs[$key] = rawurlencode($value);
                }
            }
            $url->path = Net_URL::resolvePath(implode('/', $dirs));
        }
        $this->baseUrl = $url->getURL();

        $this->list = array();

        // Instantiate the new MP3_If object
        $this->mp3 = new MP3_Id();

        $this->debug = $debug;
        $this->parse();
    }

    // }}}
    // {{{ debug()

    /**
     * Output debug message.
     *
     * @param string $msg Message.
     */
    private function debug($message)
    {
        if ($this->debug == true) {
            print  'MP3_Playlist Debug: ' . $message . "<br />\n";
        }
    }

    // }}}
    // {{{ fixPath()

    /**
     * Convert Windows directory separator to Unix style and fix ending slash.
     *
     * @param   string $dir Directory.
     *
     * @return  string result.
     */
    static public function fixPath($dir)
    {
        $dir = str_replace("\\", '/', $dir);
        if (substr($dir, -1) != '/') {
            if (PEAR_OS == 'Windows' && substr($dir, -1) != '\\') {
                $dir .= '/';
            } else {
                $dir .= '/';
            }
        }
        return $dir;
    }

    // }}}
    // {{{ generateURL()

    /**
     * Generates the URL from absolute path.
     *
     * @param   string $absPath Absolute path.
     *
     * @return  string URL.
     */
    private function generateURL($absPath)
    {
        $absPath = str_replace($this->parseDirectory, '', $absPath);
        $paths = explode('/', $absPath);
        foreach ($paths as $key => $value)
        {
            if (empty($value)) {
                continue;
            }

            $paths[$key] = rawurlencode($value);
        }

        return $this->baseUrl . implode('/', $paths);
    }

    // }}}
    // {{{ parse()

    /**
     * Parses the MP3 directory defined in the constructor
     *
     * Appends to the files the append directory of MP3 files including
     * whitespace (necessary for streaming if the append path is a URL) adds
     * the basic list to the List and Merged Array List is used for basic
     * playlist writing while Merged will be used for XML and a more
     * complete output including the ID3 tags.
     *
     * @param   bool $dir (optional) Directory to parse, this param use for recursive
     *                               purpose, default FALSE.
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     * @see     MP3_Playlist::fixPath()
     * @see     MP3_Playlist::generateURL()
     */
    private function parse($dir = false)
    {
        if ($dir == false) {
            $dir = $this->parseDirectory;
        }

        // Attempt to open directory.
        $dh = @opendir($dir);
        if (!$dh) {
            throw new PEAR_Exception(self::E_CANNOT_OPENDIR, -1);
        }

        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $abspath = self::fixPath($dir) . $entry;
            $this->debug('Got entry "' . $abspath . '"');

            if (is_dir($abspath)) {
                $this->debug('Read subdirectory "' . $abspath . '"');
                $this->parse($abspath);
                continue;
            } else if (strtolower(substr($entry, -4)) == '.mp3') {
                $url = $this->generateURL($abspath);
                $this->list[] = $url;

                // preparing the $this->merged array which will contain both the
                // $this->list basic information and more ID3 tags
                $this->merged[] = array(
                    'BaseName'  => basename($entry),
                    'FullPath'  => $abspath,
                    'URL'       => $url,
                );
            }
        }

        $this->debug('Merged list <pre>' . print_r($this->merged, true) . '</pre>');
        return true;
    }

    // }}}
    // {{{ mergeList()

    /**
     * Merges the values from the parsed known elements like file location
     * and append URL with the ID3 tags
     *
     * Sets the value to Not Available if null in the ID3 tag.
     * Trims results to avoid error in the XML and XHTML display.
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     * @see     MP3_Id::read()
     */
    private function mergeList()
    {
        // We will use this in case someone tries to call a method like
        // makeXHTML before merging
        $this->isMerged = true;

        foreach($this->merged as $id => $detail) {
            // input debug
            $this->debug('Read file "' . $detail['FullPath'] . '"');

            // reading each of the files in our array using the fullpath
            $temp = $this->mp3->read($detail['FullPath']);

            //if for some reason mp3 returns an error we raise a pear error
            if (PEAR::isError($temp)) {
                throw new PEAR_Exception(self::MP3_PLAYLIST_INVALID_MERG, -1);
            }

            // Adding the tags name, artist, genre and album to the merged
            // array. If not available, which happens in many mp3 files
            // add not available.
            // We trim to avoid any kind of problem with the XML XHTML or
            // RSS generation
            $title = trim($this->mp3->getTag('name'));
            if ($title && !empty($title)) {
                $this->merged[$id]['title'] = trim($title);
            } else {
                $this->merged[$id]['title'] = $detail['BaseName'];
            }

            $artist = trim($this->mp3->getTag('artists'));
            if ($artist && !empty($artist)){
                $this->merged[$id]['artist'] = $artist;
            } else {
                $this->merged[$id]['artist'] = 'Unknown Artist';
            }

            $album = trim($this->mp3->getTag('album'));
            if ($album && !empty($album)) {
                $this->merged[$id]['album'] = $album;
            } else {
                $this->merged[$id]['album'] = 'Not Available';
            }

            $genre = trim($this->mp3->getTag('genre'));
            if ($genre && !empty($genre)) {
                $this->merged[$id]['genre'] = $genre;
            } else {
                $this->merged[$id]['genre'] = 'Not Available';
            }
        }

        // output debug
        $this->debug('Merged list <pre>' . print_r($this->merged, true) . '</pre>');

        return true;
    }

    // }}}
    // {{{ getList()

    /**
     * Get list of songs in an array, formatted after parsing.
     *
     * @return  array
     */
    public function getList()
    {
        return $this->list;
    }

    // }}}
    // {{{ getMerged()

    /**
     * Get list of files including append directory, location and ID3 tags for XML
     * and other manipulation.
     *
     * @return  array
     * @see     MP3_Playlist::mergeList()
     */
    public function getMerged()
    {
        if (!$this->isMerged) {
            $this->mergeList();
        }

        return $this->merged;
    }

    // }}}
    // {{{ make()

    /**
     * Generates the playlist according to selected format.
     *
     * @param   string $type Format type, use one of constants
     * <pre>
     * MP3_Playlist::TYPE_M3U       M3U playlist
     * MP3_Playlist::TYPE_SMILL     SMILL playlist
     * MP3_Playlist::TYPE_XML       XML format
     * MP3_Playlist::TYPE_XHTML     XHTML output
     * MP3_Playlist::TYPE_RSS       RSS output
     * MP3_Playlist::TYPE_SQLITE    Save to SQLite database
     * </pre>
     * @param   array $params (optional) Parameters, an associative array contains
     *                                   values depend on the format.
     * @param   bool $shuffle (optional) Whether to shuffle the list or not.
     *                                   This parameter only affect on formats
     *                                   M3U, SMILL and XHTML
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     * @see     MP3_Playlist_Common::__construct()
     * @see     MP3_Playlist_M3U::make()
     * @see     MP3_Playlist_SMIL::make()
     * @see     MP3_Playlist_XML::make()
     * @see     MP3_Playlist_XHTML::make()
     * @see     MP3_Playlist_RSS::make()
     * @see     MP3_Playlist_SQLite::make()
     */
    public function make($type, $params = array(), $shuffle = false)
    {
        $type = strtolower($type);
        if (!isset(self::$drivers[$type])) {
            throw new PEAR_Exception(self::E_UNSUPPORTED_TYPE);
        }

        $file = 'MP3/Playlist/' . self::$drivers[$type]['file'];
        $class = self::$drivers[$type]['class'];

        include_once($file);
        $this->playlist = new $class($this, $shuffle, $this->debug);
        return $this->playlist->make($params);
    }

    // }}}
    // {{{ save()

    /**
     * Save the generated playlist into file.
     *
     * @param   string $filename (optional) A filename for save
     *                                     (without file extension).
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     * @see     MP3_Playlist_Common::save()
     */
    public function save($filename = 'playlist')
    {
        if (!is_object($this->playlist) || !is_a($this->playlist, 'MP3_Playlist_Common')) {
            throw new PEAR_Exception(self::E_INVALID_PLAYLIST);
        }

        return $this->playlist->save($this->outputDirectory, $filename);
    }

    // }}}
    // {{{ send()

    /**
     * Send the generated playlist to browser directly.
     *
     * @param   string $filename (optional) A filename for send
     *                                      (without file extension).
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     * @see     MP3_Playlist_Common::send()
     */
    public function send($filename = 'playlist')
    {
        if (!is_object($this->playlist) || !is_a($this->playlist, 'MP3_Playlist_Common')) {
            throw new PEAR_Exception(self::E_INVALID_PLAYLIST);
        }

        return $this->playlist->send($filename);
    }

    // }}}
    // {{{ show()

    /**
     * Show the generated playlist result
     *
     * @throws  PEAR_Exception
     * @see     MP3_Playlist_Common::show()
     */
    public function show()
    {
        if (!is_object($this->playlist) || !is_a($this->playlist, 'MP3_Playlist_Common')) {
            throw new PEAR_Exception(self::E_INVALID_PLAYLIST);
        }

        return $this->playlist->show();
    }

    // }}}
}

// }}}

/*
 * Local variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
