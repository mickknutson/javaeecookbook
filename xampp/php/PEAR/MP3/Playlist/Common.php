<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_Common class.
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
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   Copyright (c) 2004-2005 David Costa
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     CVS: $Id: Common.php,v 1.1 2005/10/02 17:38:56 firman Exp $
 */

// }}}
// {{{ Class: MP3_Playlist_Common

/**
 * Abstract class of MP3_Playlist drivers class.
 *
 * @category    File Formats
 * @package     MP3_Playlist
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   Copyright (c) 2004-2005 David Costa
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     Release: 0.5.1alpha1
 * @abstract
 */
abstract class MP3_Playlist_Common
{
    // {{{ Object Variables

    /**
     * Debug flag.
     * @var bool
     */
    protected $debug = false;

    /**
     * The playlist result just made.
     * @var string
     */
    protected $result = '';

    /**
     * Mime type of output.
     * @var string
     */
    protected $mimeType = '';

    /**
     * File extension (without dot).
     * @var string
     */
    protected $fileExtension = '';

    /**
     * Whether the result is viewable by browser or not.
     * @var bool
     */
    protected $isViewable = true;

    /**
     * Whether the result is saveable or not.
     * @var bool
     */
    protected $isSaveable = true;

    /**
     * Whether the result is sendable or not.
     * @var bool
     */
    protected $isSendable = true;

    /**
     * Whether to create shuffle list or not.
     * @var bool
     */
    protected $isShuffle = false;


    /**
     * List of song files.
     */
    protected $list = array();

    /**
     * Whether is required to merge list or not to generate
     * the playlist.
     * @var bool
     */
    protected $isRequiredMerged = false;

    /**
     * List of files including append directory, location and ID3 tags for XML
     * and other manipulation
     * @var array
     */
    protected $merged = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * @param   object  $playlist An instance of MP3_Playlist object.
     * @param   bool    $shuffle  Whether to shuffle the playlist or not.
     * @param   bool    $debug    Whether to show debug message or not.
     *
     * @see     MP3_Playlist::getList()
     * @see     MP3_Playlist::getMerged()
     */
    public function __construct(MP3_Playlist $playlist, $shuffle = false,
                                $debug = false)
    {
        $this->playlist = $playlist;
        $this->isShuffle = $shuffle;
        $this->debug = $debug;
        $this->list = $this->playlist->getList();
        if ($this->isRequiredMerged) {
            $this->merged = $this->playlist->getMerged();
        }
    }

    // }}}
    // {{{ debug()

    /**
     * Output debug message.
     *
     * @param   string $msg Message.
     */
    final private function debug($message)
    {
        if ($this->debug == true) {
            print  'MP3_Playlist Debug: ' . $message . "<br />\n";
        }
    }

    // }}}
    // {{{ make()

    /**
     * Generates the playlist.
     *
     * @param   array $params Make parameters.
     */
    public function make($params = array())
    {
        // Should be implement by extends class.
    }

    // }}}
    // {{{ save()

    /**
     * Save the generates playlist into file.
     *
     * @param   string $dir Directory
     * @param   string $filename Filename
     *
     * @throws  PEAR_Exception
     * @return  bool TRUE
     */
    final public function save($dir, $filename)
    {
        if (!$this->isSaveable) {
            throw new PEAR_Exception(MP3_Playlist::E_NOT_SAVEABLE);
        }

        $file = $dir . '/' . $filename . '.' . $this->fileExtension;

        // trying to create the file in the relevant output directory
        $fp = @fopen($file,'w+');

        // checking if we cannot create the file return an error with details
        if (!@$fp) {
            throw new PEAR_Exception(MP3_Playlist::E_CANNOT_SAVEFILE .
                                     "filename: $file in " .
                                     $this->outputDirectory . ' ' .
                                     'Possible Problem: Permissions',
                                     -1);
        }

        // writing and closing the file
        fwrite($fp, $this->result);
        fclose($fp);
        return true;
    }

    // }}}
    // {{{ send()

    /**
     * Send the generated playlist to browser direclty.
     *
     * @param   string $filename Filename, if the format not viewable by browser
     *                         send method will be switch into download mode.
     * @throws  PEAR_Exception
     * @return  bool TRUE
     */
    final public function send($filename)
    {
        if (!$this->isSendable) {
            throw new PEAR_Exception(MP3_Playlist::E_NOT_SENDABLE, -1);
        }

        if (headers_sent()) {
            throw new PEAR_Exception(MP3_Playlist::E_CANNOT_SENDHEADER, -1);
        }

        header('Content-Type: ' . $this->mimeType);

        if (!$this->isViewable) {
            header('Content-Disposition: attachment; filename="' . $filename . '.' . $this->fileExtension . '"');
            header('Pragma: no-cache');
        }

        print $this->result;
        return true;
    }

    // }}}
    // {{{ show()

    /**
     * Show the generated playlist result.
     */
    public function show()
    {
        print htmlspecialchars($this->result);
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