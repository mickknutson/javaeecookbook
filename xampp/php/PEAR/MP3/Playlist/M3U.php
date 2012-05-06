<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_M3U class.
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
 * @version     CVS: $Id: M3U.php,v 1.1 2005/10/02 17:38:56 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load MP3_Playlist_Common as the base class.
 */
require_once 'MP3/Playlist/Common.php';

// }}}
// {{{ Class: MP3_Playlist_M3U

/**
 * Class MP3_Playlist_M3U, generates the M3U playlist format.
 *
 * The M3U playlist format is supported by Winamp, XMMS, Noatunes
 * and several other players.
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
class MP3_Playlist_M3U extends MP3_Playlist_Common
{
    // {{{ Object Properties

    /**
     * Mime type of output.
     * @var string
     */
    protected $mimeType = 'audio/x-mpegurl';

    /**
     * File extension (without dot).
     * @var string
     */
    protected $fileExtension = 'm3u';

    /**
     * Force M3U format is not viewable by browser.
     * @var bool
     */
    protected $isViewable = false;

    // }}}
    // {{{ make()

    /**
     * Generates the M3U playlist format.
     *
     * @param   array $params (optional) No parameters, ignore this.
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {
        // checking if we need to shuffle the filelist inside the array or not
        if ($this->isShuffle == true) {
            shuffle($this->list);
        }

        // array glue keeping one file per line as per m3u standards
        $this->result = implode("\n", $this->list);

        return true;
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