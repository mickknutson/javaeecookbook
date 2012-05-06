<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_XHTML class.
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
 * @version     CVS: $Id: XHTML.php,v 1.1 2005/10/02 17:38:56 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load MP3_Playlist_Common as the base class.
 */
require_once 'MP3/Playlist/Common.php';

// }}}
// {{{ Class: MP3_Playlist_XHMTL

/**
 * Class MP3_Playlist_XHTML, generates the XHTML page for playlist.
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
class MP3_Playlist_XHTML extends MP3_Playlist_Common
{
    // {{{ Object Properties

    /**
     * Mime type of output.
     * @var string
     */
    protected $mimeType = 'application/xhtml+xml';

    /**
     * File extension (without dot).
     * @var string
     */
    protected $fileExtension = 'html';

    /**
     * XHTML required the merged list.
     * @var bool
     */
    protected $isRequiredMerged = true;

    // }}}
    // {{{ make()

    /**
     * Generate a valid XHTML playlist with link to the given URL defined
     * on parsing
     *
     * It can also generates the mere tables and therefore allows the user
     * to customize the look and feel
     *
     * @param   array $params Make parameters.
     * <pre>
     * title    string  Page title.
     * fullpage bool    (optional) If TRUE it will generate a full XHTML page,
     *                  otherwise only tables, default is TRUE.
     * </pre>
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {
        if (!isset($params['title'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "title" as page title', -1);
        }

        $title = $params['title'];
        $fullpage = isset($params['fullpage']) ? $params['fullpage'] : true;

        // Preparing the numbers
        $numloop = 0;

        // Checking if we are producing a shuffled playlist
        if ($this->isShuffle == true)  {
            shuffle($this->merged);
        }

        $xhtmlLoop = '';
        // creating the tables
        foreach ($this->merged as $prepared) {
            $prepared['title'] = htmlspecialchars($prepared['title']);
            $prepared['artist'] = htmlspecialchars($prepared['artist']);
            $prepared['album'] = htmlspecialchars($prepared['album']);
            $prepared['genre'] = htmlspecialchars($prepared['genre']);

            // adding random colors in the list
            $tag = '<tr class="' .($numloop%2 ? 'wh' : 'bl' ) . '">';
            $numloop++;

            $xhtmlLoop .= <<<LOOP
$tag
<td class="left" title="$numloop">$numloop</td>
<td title="{$prepared['title']}"><div style="height:1.2em; width:100%; overflow:hidden;"><a href="{$prepared['URL']}">{$prepared['title']}</a></div></td>
<td title="{$prepared['artist']}"><div style="height:1.2em; width:100%; overflow:hidden;">{$prepared['artist']}</div></td>
<td title="{$prepared['album']}"><div style="height:1.2em; width:100%; overflow:hidden;">{$prepared['album']}</div></td>
<td title="{$prepared['genre']}">{$prepared['genre']}</td>
</tr>
LOOP;
        }

        // XHTML standard headers plus the tables
        $xhtmlBody = <<<XML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
 <head>
  <title>$title</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
   body {font-family:lucida grande, arial;}
   .box {margin:0 auto 0 auto; font-size:11px;}
   .header {text-align:center; font-size:13px;}
   table {border:1px solid #666; margin:0 auto 0 auto; font-size:11px;}
   td {border:1px #d9d9d9; border-style: none none none solid; padding:2px 5px 2px 5px;}
   .top {background:url(images/top.png);}
   .left {border:0px; text-align:right;}
   .right {text-align:right}
   .wh {background:#fff;}
   .bl {background:#edf3fe;}
  </style>
 </head>
 <body>
  <div class="box">
   <div class="header" title="$title">$title</div><br/>
   <table cellspacing="0">
    <tr class="top">
     <td class="left">&nbsp;</td>
     <td title ="Song&nbsp;Name">Song&nbsp;Name</td>
     <td title="Artist">Artist</td>
     <td title="Album">Album</td>
     <td title="Genre">Genre</td>
    </tr>
    $xhtmlLoop
   </table>
    <p>
      <a href="http://validator.w3.org/check?uri=referer"><img
          src="http://www.w3.org/Icons/valid-xhtml11"
          alt="Valid XHTML 1.1!" height="31" width="88" /></a>
    </p>
    <br/>
   </div>
 </body>
</html>
XML;

        if ($fullpage == true) {
            // display the page
            $this->result = $xhtmlBody;
        } else {
            // display the tables only
            $this->result = $xhtmlLoop;
        }
        return true;
    }

    // }}}
}

// }}}