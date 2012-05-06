<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_RSS class.
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
 * @version     CVS: $Id: RSS.php,v 1.1 2005/10/02 17:38:56 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load MP3_Playlist_Common as the base class.
 */
require_once 'MP3/Playlist/Common.php';

// }}}
// {{{ Class: MP3_Playlist_RSS

/**
 * Class MP3_Playlist_RSS, generates the playlist RSS feed.
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
class MP3_Playlist_RSS extends MP3_Playlist_Common
{
    // {{{ Object Properties

    /**
     * Mime type of output.
     * @var string
     */
    protected $mimeType = 'application/xml';

    /**
     * File extension (without dot).
     * @var string
     */
    protected $fileExtension = 'rss';

    /**
     * RSS required the merged list.
     * @var bool
     */
    protected $isRequiredMerged = true;

    // }}}
    // {{{ make()

    /**
     * Generate a valid RSS feed from a playlist
     *
     * User should define as parameters the title of the feed description
     * and link for each song the rss produced will include the title and
     * link and keep within the description the other information available
     * namely the album, artist, and genre.
     *
     * @param   array   $params Make parameters.
     * <pre>
     * title        string  Feed main title
     * description  string  Feed description
     * link         string  Link main description
     * </pre>
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {

        if (!isset($params['title'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "title" as feed title', -1);
        }


        if (!isset($params['description'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "description" as feed description', -1);
        }


        if (!isset($params['link'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "link" as link main description', -1);
        }

        $title = $params['title'];
        $description = isset($params['description']) ? $params['description'] : '';
        $link = isset($params['link']) ? $params['link'] : '';

        $rssbody = '';
        foreach ($this->merged as $prepared) {
            // formatting for RSS so we avoid errors with & and other signs
            $prepared['title']  = htmlspecialchars($prepared['title']);
            $prepared['URL']    = htmlspecialchars($prepared['URL']);
            $prepared['artist'] = htmlspecialchars($prepared['artist']);
            $prepared['album']  = htmlspecialchars($prepared['album']);
            $prepared['genre']  = htmlspecialchars($prepared['genre']);

            // basic RSS entry for each of the songs
            $rssbody .= <<<XMLBODY
<item>
<title>$prepared[title]</title>
<link>$prepared[URL]</link>
<description>$prepared[artist] - $prepared[album] - $prepared[genre]</description>
</item>
XMLBODY;
        }


        // standard RSS header with the parameters specified by the user
        $this->result = <<<XMLHEADER
<?xml version="1.0" encoding="iso-8859-1"?>
<rss version="0.91">
<channel>
<title> $title </title>
<description> $description </description>
<link>$link</link>
$rssbody
</channel>
</rss>
XMLHEADER;

        return true;
    }

    // }}}
}

// }}}