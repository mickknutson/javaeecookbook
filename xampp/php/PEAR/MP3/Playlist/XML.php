<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_XML class.
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
 * @version     CVS: $Id: XML.php,v 1.1 2005/10/02 17:38:57 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load MP3_Playlist_Common as the base class.
 */
require_once 'MP3/Playlist/Common.php';

// }}}
// {{{ Class: MP3_Playlist_XML

/**
 * Class MP3_Playlist_XML, generate the XML document for playlist.
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
class MP3_Playlist_XML extends MP3_Playlist_Common
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
    protected $fileExtension = 'xml';

    /**
     * XML required the merged list.
     * @var bool
     */
    protected $isRequiredMerged = true;

    // }}}
    // {{{ preXML()

    /**
     * Prepares the xml format for the loop used on makexml
     *
     * @param   string $value  character data that goes into the XML element
     * @param   string $key    the name of the XML element
     *
     * @return  TRUE
     */
    private function preXML($value, $key)
    {
        $this->result .= "    <$key>" . htmlentities($value) . "</$key>\n";
        return true;
    }

    // }}}
    // {{{ make()

    /**
     * Generates a valid XML with the playlist values.
     *
     * @param   array $params (optional) No parameters, ignore this.
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {
        // Defining XML headers
        $this->result = '<?xml version="1.0" encoding="ISO-8859-1" ?>'."\n";
        $this->result .= "<playlist>\n";

        // Adding the prexml formatted tags to each of the array members
        // adding also the track tag
        foreach ($this->merged as $list){
            $this->result .= "  <track>\n";
            array_walk($list, array(&$this, 'preXML'));
            $this->result .= "  </track>\n";
        }
        $this->result .= "</playlist>";

        return true;
    }

    // }}}
}

// }}}