<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_SMIL class.
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
 * @version     CVS: $Id: SMIL.php,v 1.1 2005/10/02 17:38:56 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load MP3_Playlist_Common as the base class.
 */
require_once 'MP3/Playlist/Common.php';

// }}}
// {{{ Class: MP3_Playlist_SMIL

/**
 * Class MP3_Playlist_SMILL, generates the SMIL (SMIL Multimedia Persentation)
 * playlist.
 *
 * The Smil playlist format is supported by Real Media Player, Quicktime.
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
class MP3_Playlist_SMIL extends MP3_Playlist_Common
{
    // {{{ Object Properties

    /**
     * Mime type of output.
     * @var string
     */
    protected $mimeType = 'application/smil';

    /**
     * File extension (without dot).
     * @var string
     */
    protected $fileExtension = 'smil';

    /**
     * Force M3U format is not viewable by browser.
     * @var bool
     */
    protected $isViewable = false;

    // }}}
    // {{{ make()

    /**
     * Generates the SMIL (SMIL Multimedia Persentation) playlist.
     *
     * @param   array $params (optional) No parameters, ignore this.
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {
        if ($this->isShuffle == true) {
            shuffle($this->list);
        }

        $AudioTags = array();
        foreach ($this->list as $entry) {
            // add a new <audio> line to the array
            $AudioTags[] = '<audio src="'.$entry.'" />';
        }

        // Glue the array with \n so that we obtain a string of X lines
        // containing all files
        $AudioTags = implode("\n", $AudioTags);

        // put together the smil
        $this->result = <<<SMIL
<smil>
<body>
<seq>
$AudioTags
</seq>
</body>
</smil>
SMIL;

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