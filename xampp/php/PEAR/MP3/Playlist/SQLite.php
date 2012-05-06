<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains MP3_Playlist_SQLite class.
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
 * @version     CVS: $Id: SQLite.php,v 1.1 2005/10/02 17:38:56 firman Exp $
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
 * Class MP3_Playlist_SMILL for generate and stores an sqlite based
 * playlist.
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
class MP3_Playlist_SQLite extends MP3_Playlist_Common
{
    // {{{ Object Properties

    /**
     * Force SQLite format is not viewable by browser.
     * @var bool
     */
    protected $isViewable = false;

    /**
     * Force SQLite format is not saveable.
     * @var bool
     */
    protected $isSaveable = false;

    /**
     * Force SQLite format is not sendable.
     * @var bool
     */
    protected $isSendable = false;

    /**
     * Default table columns.
     * @var array
     */
    protected $columns = array(
        'fullpath'  => 'fullpath',
        'url'       => 'url',
        'title'     => 'title',
        'artist'    => 'artist',
        'album'     => 'album',
        'genre'     => 'genre'
    );

    // }}}
    // {{{ make()

    /**
     * Generate and stores an sqlite based playlist
     *
     * Optionally creates a database and the table or can be set to
     * add the extended playlist in an existing database and table.
     *
     * @param   array   $params Make parameters.
     * <pre>
     * db           string  SQLite database name
     * table        string  SQLite database table name
     * maketable    bool    (optional) Should the table be created or not,
     *                                 default is TRUE
     * columns      array   (optional) Define the 6 column names yourself.
     * </pre>
     *
     * @return  bool TRUE
     */
    public function make($params = array())
    {
        if (!isset($params['db'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "db" as database name', -1);
        }

        if (!isset($params['table'])) {
            throw new PEAR_Exception(MP3_Playlist::E_REQUIRED_PARAM .
                                     ' "table" as database table name', -1);
        }

        $dbname = $params['db'];
        $table = $params['table'];
        $maketable = isset($params['maketable']) ? $params['maketable'] : true;
        $columns = isset($params['columns']) ? $params['columns'] : false;

        // check if the user defined his own columns details
        if (is_array($columns)) {
            $this->columns = array_merge($this->columns, $columns);
        }


        $error ='';

        // attempt to open the database, if it doesn't exist it will be created
        $db = @sqlite_open($dbname, 0666, $error);

        // return an error if we cannot open the sqlite database
        if (!@$db) {
            throw new PEAR_Exception(MP3_Playlist::E_CANNOT_OPENDB .
                                      "more info: $error", -1);
        }

        // creating the table is optional so we check first if the the value is TRUE
        if ($maketable == true) {
            // if true we create the table
            $query = @sqlite_query($db,
                                    "CREATE table $table (" .
                                    ' id INTEGER PRIMARY KEY, ' .
                                    " {$this->columns['fullpath']} varchar(200), " .
                                    " {$this->columns['url']} varchar(200), " .
                                    " {$this->columns['title']} title varchar(200), " .
                                    " {$this->columns['artist']} varchar(200), " .
                                    " {$this->columns['album']} varchar (200), " .
                                    " {$this->columns['genre']} varchar(200) " .
                                    ')'
                                   );


            // return an error in case that the table cannot be created
            if (!@$query) {
                throw new PEAR_Exception(MP3_Playlist::E_CANNOT_CREATETABLE, -1);
            }
        }

        foreach ($this->merged as $prepared){
            // escaping each of the values
            $fullpath   = sqlite_escape_string($prepared['FullPath']);
            $url        = sqlite_escape_string($prepared['URL']);
            $title      = sqlite_escape_string($prepared['title']);
            $artist     = sqlite_escape_string($prepared['artist']);
            $album      = sqlite_escape_string($prepared['album']);
            $genre      = sqlite_escape_string($prepared['genre']);

            // insert query preparation
            $sql = "INSERT INTO $table (
                        {$this->columns['fullpath']},
                        {$this->columns['url']},
                        {$this->columns['title']},
                        {$this->columns['artist']},
                        {$this->columns['album']},
                        {$this->columns['genre']}
                    )
                    VALUES (
                        '$fullpath',
                        '$url',
                        '$title',
                        '$artist',
                        '$album',
                        '$genre'
                    )";

            // executing the query
            $query = @sqlite_query($db, $sql);

            // if we do have a problem with the query return an error with the
            // debug information this might occur if the user passes some invalid
            // values for the columns
            if (!@$query) {
                throw new PEAR_Exception(MP3_Playlist::E_CANNOT_INSERT .
                                         "debug info print_r ($sql)", -1);
            }
        }

        echo 'List saved on sqlite<br />';
        return true;
    }

    // }}}
}

// }}}