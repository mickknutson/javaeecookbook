<?php
/**
 * XML::sql2xml
 *
 * PHP version 4
 *
 * @category   XML
 * @package    XML_sql2xml
 * @author     Christian Stocker <chregu@php.net>
 * @copyright  2001 - 2008 Christian Stocker
 * @license    BSD, revised
 * @version    CVS: $Id: sql2xml.php,v 1.17 2008/03/24 15:51:51 dufuz Exp $
 * @link       http://pear.php.net/package/XML_sql2xml
 */

/**
* This class takes a PEAR::DB-Result Object, a sql-query-string or an array
*  and returns a xml-representation of it.
*
* TODO
*   -encoding etc, options for header
*   -ERROR CHECKING
*
* Usage example
*
* include_once ("DB.php");
* include_once("XML/sql2xml.php");
* $db = DB::connect("mysql://root@localhost/xmltest");
* $sql2xml = new xml_sql2xml();
* //the next one is only needed, if you need others than the default
* $sql2xml->setEncoding("ISO-8859-1","UTF-8");
* $result = $db->query("select * from bands");
* $xmlstring = $sql2xml->getXML($result);
*
* or
*
* include_once ("DB.php");
* include_once("XML/sql2xml.php");
* $sql2xml = new xml_sql2xml("mysql://root@localhost/xmltest");
* $sql2xml->Add("select * from bands");
* $xmlstring = $sql2xml->getXML();
*
* More documentation and a tutorial/how-to can be found at
*   http://php.chregu.tv/sql2xml
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: sql2xml.php,v 1.17 2008/03/24 15:51:51 dufuz Exp $
* @package  XML
*/
class XML_sql2xml
{
    /**
    * If joined-tables should be output nested.
    *  Means, if you have joined two or more queries, the later
    *   specified tables will be nested within the result of the former
    *   table.
    *   Works at the moment only with mysql automagically. For other RDBMS
    *   you have to provide your table-relations by hand (see user_tableinfo)
    *
    * @var  boolean
    * @see  $user_tableinfo, doSql2Xml(), doArray2Xml();
    */
    var $nested = True;

    /**
    * Name of the tag element for resultsets
    *
    * @var  string
    * @see  insertNewResult()
    */
    var $tagNameResult = "result";

    /**
    * Name of the tag element for rows
    *
    * @var  string
    * @see  insertNewRow()
    */
    var $tagNameRow = "row";

    /**
    *
    * @var   object PEAR::DB
    * @access private
    */
    var $db = Null;

    /**
    * Options to be used in extended Classes (for example in sql2xml_ext).
    * They are passed with SetOptions as an array (arrary("user_options" = array());
    *  and can then be accessed with $this->user_options["bla"] from your
    *  extended classes for additional features.
    *  This array is not use in this base class, it's only for passing easy parameters
    *  to extended classes.
    *
    * @var      array
    */
    var $user_options = array();

    /**
    * The DomDocument Object to be used in the whole class
    *
    * @var      object  DomDocument
    * @access    private
    */
    var $xmldoc;

    /**
    * The Root of the domxml object
    * I'm not sure, if we need this as a class variable....
    * could be replaced by domxml_root($this->xmldoc);
    *
    * @var      object DomNode
    * @access    private
    */
    var $xmlroot;

    /**
    * This array is used to give the structure of your database to the class.
    *  It's especially useful, if you don't use mysql, since other RDBMS than
    *  mysql are not able at the moment to provide the right information about
    *  your database structure within the query. And if you have more than 2
    *  tables joined in the sql it's also not possible for mysql to find out
    *  your real relations.
    *  The parameters are the same as in fieldInfo from the PEAR::DB and some
    *   additional ones. Here they come:
    *  From PEAR::DB->fieldinfo:
    *
    *    $tableInfo[$i]["table"]    : the table, which field #$i belongs to.
    *           for some rdbms/comples queries and with arrays, it's impossible
    *           to find out to which table the field actually belongs. You can
    *           specify it here more accurate. Or if you want, that one fields
    *           belongs to another table, than it actually says (yes, there's
    *           use for that, see the upcoming tutorial ...)
    *
    *    $tableInfo[$i]["name"]     : the name of field #$i. if you want another
    *           name for the tag, than the query or your array provides, assign
    *           it here.
    *
    *   Additional info
    *     $tableInfo["parent_key"][$table]  : index of the parent key for $table.
    *           this is the field, where the programm looks for changes, if this
    *           field changes, it assumes, that we need a new "rowset" in the
    *           parent table.
    *
    *     $tableInfo["parent_table"][$table]: name of the parent table for $table.
    *
    * @var      array
    * @access    private
    */
    var $user_tableInfo = array();

    /**
    * the encoding type, the input from the db has
    */
    var $encoding_from  = 'ISO-8859-1';

    /**
    * the encoding type, the output in the xml should have
    * (note that domxml at the moment only support UTF-8, or at least it looks like)
    */
    var $encoding_to = 'UTF-8';

    var $tagname = 'tagname';

    /**
    * Constructor
    * The Constructor can take a Pear::DB "data source name" (eg.
    *  "mysql://user:passwd@localhost/dbname") and will then connect
    *  to the DB, or a PEAR::DB object link, if you already connected
    *  the db before.
    "  If you provide nothing as $dsn, you only can later add stuff with
    *   a pear::db-resultset or as an array. providing sql-strings will
    *   not work.
    * the $root param is used, if you want to provide another name for your
    *  root-tag than "root". if you give an empty string (""), there will be no
    *  root element created here, but only when you add a resultset/array/sql-string.
    *  And the first tag of this result is used as the root tag.
    *
    * @param  mixed $dsn    PEAR::DB "data source name" or object DB object
    * @param  string $root  the name of the xml-doc root element.
    * @access   public
    */
    function XML_sql2xml($dsn = Null, $root = 'root')
    {
        // if it's a string, then it must be a dsn-identifier;
        if (is_string($dsn)) {
            include_once 'DB.php';
            $this->db = DB::Connect($dsn);
            if (DB::isError($this->db)) {
                print "The given dsn for XML_sql2xml was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                return new DB_Error($this->db->code,PEAR_ERROR_DIE);
            }

        } elseif (is_object($dsn) && DB::isError($dsn)) {
            print "The given param for XML_sql2xml was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
            return new DB_Error($dsn->code,PEAR_ERROR_DIE);
        } elseif (strtolower(get_parent_class($dsn)) == 'db_common') {
            // if parent class is db_common, then it's already a connected identifier
            $this->db = $dsn;
        }

        $this->xmldoc = domxml_new_xmldoc('1.0');

        //oehm, seems not to work, unfortunately.... does anybody know a solution?
        $this->xmldoc->encoding = $this->encoding_to;

        if ($root) {
            $this->xmlroot = $this->xmldoc->add_root($root);
            //PHP 4.0.6 had $root->name as tagname, check for that here...
            if (!isset($this->xmlroot->{$this->tagname})) {
                $this->tagname = 'name';
            }
        }

    }

    /**
    * General method for adding new resultsets to the xml-object
    *  Give a sql-query-string, a pear::db_result object or an array as
    *  input parameter, and the method calls the appropriate method for this
    *  input and adds this to $this->xmldoc
    *
    * @param    string sql-string, or object db_result, or array
    * @param    mixed additional parameters for the following functions
    * @access   public
    * @see      addResult(), addSql(), addArray(), addXmlFile()
    */
    function add ($resultset, $params = null)
    {
        // if string, then it's a query, a xml-file or a xml-string...
        if (is_string($resultset)) {
            if (preg_match("/\.xml$/",$resultset)) {
                $this->AddXmlFile($resultset,$params);
            } elseif (preg_match("/.*select.*from.*/i" ,  $resultset)) {
                $this->AddSql($resultset);
            } else {
                $this->AddXmlString($resultset);
            }
        } elseif (is_array($resultset)) {
            // if array, then it's an array...
            $this->AddArray($resultset);
        }

        if (strtolower(get_class($resultset)) == 'db_result') {
            $this->AddResult($resultset);
        }
    }

    /**
    * Adds the content of a xml-file to $this->xmldoc, on the same level
    * as a normal resultset (mostly just below <root>)
    *
    * @param    string filename
    * @param    mixed xpath  either a string with the xpath expression or an array with "xpath"=>xpath expression  and "root"=tag/subtag/etc, which are the tags to be inserted before the result
    * @access   public
    * @see      doXmlString2Xml()
    */

    function addXmlFile($file, $xpath = null)
    {
        $content = file_get_contents($file);
        $this->doXmlString2Xml($content, $xpath);
    }

    /**
    * Adds the content of a xml-string to $this->xmldoc, on the same level
    * as a normal resultset (mostly just below <root>)
    *
    * @param    string xml
    * @param    mixed xpath  either a string with the xpath expression or an array with "xpath"=>xpath expression  and "root"=tag/subtag/etc, which are the tags to be inserted before the result
    * @access   public
    * @see      doXmlString2Xml()
    */
    function addXmlString($string, $xpath = null)
    {
        $this->doXmlString2Xml($string, $xpath);
    }

    /**
    * Adds an additional pear::db_result resultset to $this->xmldoc
    *
    * @param    Object db_result result from a DB-query
    * @see      doSql2Xml()
    * @access   public
    */
    function addResult($result)
    {
        $this->doSql2Xml($result);
    }

    /**
    * Adds an aditional resultset generated from an sql-statement
    *  to $this->xmldoc
    *
    * @param    string sql a string containing an sql-statement.
    * @access   public
    * @see      doSql2Xml()
    */
    function addSql($sql)
    {
        /* if there are {} expressions in the sql query, we assume it's an xpath expression to
        *   be evaluated.
        */

        if (preg_match_all ("/\{([^\}]+)\}/i",$sql,$matches)) {
            foreach ($matches[1] as $match) {
                $sql = preg_replace("#\{".preg_quote($match)."\}#  ", $this->getXpathValue($match),$sql);
            }
        }
        $result = $this->db->query($sql);

        //very strange
        if (PEAR::isError($result->result)) {
                 print "You have an SQL-Error:<br>".$result->result->userinfo;
                 print "<br>";
                new DB_Error($result->result->code,PEAR_ERROR_DIE);
        }

        $this->doSql2Xml($result);
    }

    /**
    * Adds an aditional resultset generated from an Array
    *  to $this->xmldoc
    * TODO: more explanation, how arrays are transferred
    *
    * @param    array multidimensional array.
    * @access   public
    * @see      doArray2Xml()
    */
    function addArray($array, $prefix = 'index_')
    {
        $parent_row = $this->insertNewResult($metadata);

        $array = $this->prepareArray($array, $prefix);
        if (!$array) {
            return false;
        }

        $this->DoArray2Xml($array, $parent_row);
    }

    /**
     * Makes sure the given array has no integer indices
     *
     * @param   array multidimensional array.
     * @param   prefix string to prefix integer indices
     * @access  public
     * @see     doArray()
     */
    function prepareArray($array, $prefix = 'index_')
    {
        if (!is_array($array)) {
            return false;
        }

        $array_new = array();
        foreach($array as $key => $val) {
            if (!is_string($key)) {
                $key = $prefix.$key;
            }

            if (is_array($val)) {
                $array_new[$key] = $this->prepareArray($val);
            } else {
                $array_new[$key] = $val;
            }
        }

        return $array_new;
    }

    /**
    * Returns an xml-string with a xml-representation of the resultsets.
    *
    * The resultset can be directly provided here, or if you need more than one
    * in your xml, then you have to provide each of them with add() before you
    * call getXML, but the last one can also be provided here.
    *
    * @param    mixed  $result result Object from a DB-query
    * @return   string  xml
    * @access   public
    */
    function getXML($result = null)
    {
        $xmldoc = $this->getXMLObject($result);
        return $xmldoc->dumpmem();
    }

    /**
    * Returns an xml DomDocument Object with a xml-representation of the resultsets.
    *
    * The resultset can be directly provided here, or if you need more than one
    * in your xml, then you have to provide each of them with add() before you
    * call getXMLObject, but the last one can also be provided here.
    *
    * @param    mixed $result result Object from a DB-query
    * @return   Object DomDocument
    * @access   public
    */
    function getXMLObject($result = null)
    {
        if ($result) {
            $this->add ($result);
        }
        return $this->xmldoc;
    }

    /**
    * For adding db_result-"trees" to $this->xmldoc
    * @param    Object db_result
    * @access   private
    * @see      addResult(),addSql()
    */
    function doSql2Xml($result)
    {
        if (DB::IsError($result)) {
            print "Error in file ".__FILE__." at line ".__LINE__."<br>\n";
            print $result->userinfo."<br>\n";
            new DB_Error($result->code, PEAR_ERROR_DIE);
        }

        // BE CAREFUL: if you have fields with the same name in different tables, you will get errors
        // later, since DB_FETCHMODE_ASSOC doesn't differentiate that stuff.
        $this->LastResult = &$result;

        if (!$tableInfo = $result->tableInfo(false)) {
            //emulate tableInfo. this can go away, if every db supports tableInfo
            $fetchmode = DB_FETCHMODE_ASSOC;
            $res = $result->FetchRow($fetchmode);
            $this->nested = false;
            $i = 0;

            while (list($key, $val) = each($res)) {
                $tableInfo[$i]['table']= $this->tagNameResult;
                $tableInfo[$i]['name'] = $key;
                $resFirstRow[$i] = $val;
                $i++;
            }
            $res  = $resFirstRow;
            $fetchmode = DB_FETCHMODE_ORDERED;
        } else {
            $fetchmode = DB_FETCHMODE_ORDERED;
        }

        // initialize db hierarchy...
        $parenttable = 'root';
        $tableInfo['parent_key']['root'] = 0;

        foreach ($tableInfo as $key => $value) {
            if (is_int($key)) {
                // if the sql-query had a function the table starts with a # (only in mysql i think....), then give the field the name of the table before...
                if (preg_match ("/^#/", $value['table']) || strlen($value['table']) === 0) {
                    $value['table'] = $tableInfo[($key - 1)]['table'];
                    $tableInfo[$key]['table'] = $value['table'];
                }

                if (!isset($tableInfo['parent_table'])
                    || !isset($tableInfo['parent_table'][$value['table']])
                    || is_null($tableInfo['parent_table'][$value['table']])
                ) {
                    $tableInfo['parent_key'][$value['table']] = $key;
                    $tableInfo['parent_table'][$value['table']] = $parenttable;
                    $parenttable = $value['table'] ;
                }

            }
            //if you need more tableInfo for later use you can write a function addTableInfo..
            $this->addTableInfo($key, $value, $tableInfo);
        }

        // end initialize

        // if user made some own tableInfo data, merge them here.
        if ($this->user_tableInfo) {
            $tableInfo = $this->array_merge_clobber($tableInfo,$this->user_tableInfo);
        }
        $parent['root'] = $this->insertNewResult($tableInfo);

        //initialize $resold to get rid of warning messages;
        $resold[0] = "ThisValueIsImpossibleForTheFirstFieldInTheFirstRow";

        while ($res = $result->FetchRow($fetchmode)) {
            while (list($key, $val) = each($res)) {
                if ($resold[$tableInfo['parent_key'][$tableInfo[$key]['table']]] != $res[$tableInfo['parent_key'][$tableInfo[$key]['table']]] || !$this->nested) {
                    if ($tableInfo['parent_key'][$tableInfo[$key]['table']] == $key ) {
                        if ($this->nested || $key == 0) {
                            $parent[$tableInfo[$key]['table']] = $this->insertNewRow($parent[$tableInfo['parent_table'][$tableInfo[$key]['table']]], $res, $key, $tableInfo);
                        } else {
                            $parent[$tableInfo[$key]['table']] = $parent[$tableInfo['parent_table'][$tableInfo[$key]['table']]];
                        }

                        //set all children entries to somethin stupid
                        foreach ($tableInfo['parent_table'] as $pkey => $pvalue) {
                            if ($pvalue == $tableInfo[$key]['table']) {
                                $resold[$tableInfo['parent_key'][$pkey]]= "ThisIsJustAPlaceHolder";
                            }
                        }

                    }

                    if ( $parent[$tableInfo[$key]['table']] != null) {
                        $this->insertNewElement($parent[$tableInfo[$key]['table']], $res, $key, $tableInfo, $subrow);
                    }

                }
            }

            $resold = $res;
            unset($subrow);
        }

        return $this->xmldoc;
    }

    /**
     * For adding whole arrays to $this->xmldoc
     *
     * @param    array
     * @param    Object domNode
     * @access   private
     * @see      addArray()
     */
    function DoArray2Xml ($array, $parent)
    {
        while (list($key, $val) = each($array)) {
            $tableInfo[$key]['table'] = $this->tagNameResult;
            $tableInfo[$key]['name']  = $key;
        }

        if ($this->user_tableInfo) {
            $tableInfo = $this->array_merge_clobber($tableInfo,$this->user_tableInfo);
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_int($key)) {
                    $valuenew = array_slice($value,0,1);
                    $keynew   = array_keys($valuenew);
                    $keynew   = $keynew[0];
                } else {
                    $valuenew = $value;
                    $keynew = $key;
                }

                $rec2 = $this->insertNewRow($parent, $valuenew, $keynew, $tableInfo);
                $this->DoArray2Xml($value,$rec2);
            } else {
                $this->insertNewElement($parent, $array, $key, $tableInfo, $subrow);
            }
        }
    }

    /**
     * This method sets the options for the class
     *  One can only set variables, which are defined at the top of
     *  of this class.
     *
     * @param    array   options to be passed to the class
     * @param    boolean   if the old suboptions should be deleted
     * @access   public
     * @see      $nested,$user_options,$user_tableInfo
     */
    function setOptions($options, $delete = false)
    {
        //set options
        if (is_array($options)) {
            foreach ($options as $option => $value) {
               if (isset($this->{$option})) {
                    if (is_array($value) && ! $delete) {
                        foreach ($value as $suboption => $subvalue) {
                            $this->{$option}["$suboption"] = $subvalue;
                        }
                    } else {
                        $this->$option = $value;
                    }
                }
            }
        }
    }

    // these are the functions, which are intended to be overriden in user classes

    /**
     *
     * @param    mixed
     * @return   object  DomNode
     * @access   private
     */
    function insertNewResult(&$metadata)
    {
        if ($this->xmlroot) {
            return $this->xmlroot->new_child($this->tagNameResult, null);
        }

        $this->xmlroot = $this->xmldoc->add_root($this->tagNameResult);
        return $this->xmlroot;
    }

    /**
     *   to be written
     *
     * @param    object DomNode $parent_row
     * @param    mixed $res
     * @param    mixed $key
     * @param    mixed &metadata
     * @return   object DomNode
     * @access private
     */
    function insertNewRow($parent_row, $res, $key, &$metadata)
    {
        return  $parent_row->new_child($this->tagNameRow, null);
    }

    /**
     *   to be written
     *
     * @param    object DomNode $parent
     * @param    mixed $res
     * @param    mixed $key
     * @param    mixed &$metadata
     * @param    mixed &$subrow
     * @return   object DomNode
     * @access private
     */
    function insertNewElement($parent, $res, $key, &$metadata, &$subrow)
    {
        return  $parent->new_child($metadata[$key]['name'], $this->xml_encode($res[$key]));
    }

    /**
     *   to be written
     *
     * @param    mixed $key
     * @param    mixed $value
     * @param    mixed &$metadata
     * @access private
     */
    function addTableInfo($key, $value, &$metadata)
    {
    }

    // end functions, which are intended to be overriden in user classes

    // here come some helper functions...

    /**
    * make utf8 out of the input data and escape & with &amp; and "< " with "&lt; "
    * (we assume that when there's no space after < it's a tag, which we need in the xml)
    *  I'm not sure, if this is the standard way, but it works for me.
    *
    * @param    string text to be utfed.
    * @access private
    */
    function xml_encode ($text)
    {
        $replace = ereg_replace("&","&amp;",ereg_replace("< ","&lt; ",$text));
        if (function_exists('iconv') && isset($this->encoding_from) && isset($this->encoding_to)) {
            ini_set('track_errors', 1);
            $text = iconv($this->encoding_from, $this->encoding_to, $replace);

            if (isset($text)) {
                return $text;
            }

            if (isset($php_errormsg)) {
                $errormsg = "error: $php_errormsg";
            } else {
                $errormsg = "undefined iconv error, turn on track_errors in php.ini to get more details";
            }
            return PEAR::raiseError($errormsg, null, PEAR_ERROR_DIE);
        } else {
            $text = utf8_encode($replace);
        }

        return $text;
    }

    //taken from kc@hireability.com at http://www.php.net/manual/en/function.array-merge-recursive.php
    /**
    * There seemed to be no built in function that would merge two arrays recursively and clobber
    *   any existing key/value pairs. Array_Merge() is not recursive, and array_merge_recursive
    *   seemed to give unsatisfactory results... it would append duplicate key/values.
    *
    *   So here's a cross between array_merge and array_merge_recursive
    **/
    /**
    *
    * @param    array first array to be merged
    * @param    array second array to be merged
    * @return   array merged array
    * @access private
    */
    function array_merge_clobber($a1,$a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return false;
        }
        $newarray = $a1;
        while (list($key, $val) = each($a2)) {
            if (is_array($val) && is_array($newarray[$key])) {
                $newarray[$key] = $this->array_merge_clobber($newarray[$key], $val);
            } else {
                $newarray[$key] = $val;
            }
        }

        return $newarray;
    }

    /**
    * Adds a xml string to $this->xmldoc.
    * It's inserted on the same level as a "normal" resultset, means just as a children of <root>
    * if a xpath expression is supplied, it takes that for selecting only part of the xml-file
    *
    * @param    string xml string
    * @param    mixed xpath  either a string with the xpath expression or an array with
    *                 "xpath"=>xpath expression  and "root"=tag/subtag/etc,
    *                 which are the tags to be inserted before the result
    * @access private
    */
    function doXmlString2Xml ($string,$xpath = null)
    {
        $MainXmlString = $this->xmldoc->dumpmem();
        $string = preg_replace("/<\?xml.*\?>/","",$string);

        $MainXmlString = preg_replace("/<".$this->xmlroot->{$this->tagname}."\/>/","<".$this->xmlroot->{$this->tagname}."></".$this->xmlroot->{$this->tagname}.">",$MainXmlString);
        $MainXmlString = preg_replace("/<\/".$this->xmlroot->{$this->tagname}.">/",$string."</".$this->xmlroot->{$this->tagname}.">",$MainXmlString);

        $this->xmldoc  = xmldoc($MainXmlString);
        $this->xmlroot = $this->xmldoc->root();
    }

    /**
    * sets the encoding for the db2xml transformation
    * @param    string $encoding_from encoding to transform from
    * @param    string $encoding_to encoding to transform to
    * @access public
    */
    function setEncoding($encoding_from = 'ISO-8859-1', $encoding_to = 'UTF-8')
    {
        $this->encoding_from = $encoding_from;
        $this->encoding_to   = $encoding_to;
    }

    /**
    * @param array $parentTables parent to child relation
    * @access public
    */
    function SetParentTables($parentTables)
    {
        foreach ($parentTables as $table => $parent) {
            $table_info['parent_table'][$table] = $parent;
        }
        $this->SetOptions(array('user_tableInfo' => $table_info));
    }

    /**
    * returns the content of the first match of the xpath expression
    *
    * @param    string $expr xpath expression
    * @return   mixed content of the evaluated xpath expression
    * @access   public
    */
    function getXpathValue($expr)
    {
        $xpath = $this->xmldoc->xpath_new_context();
        $xnode = xpath_eval($xpath, $expr);

        if (isset ($xnode->nodeset[0])) {
            $firstnode = $xnode->nodeset[0];
            $children  = $firstnode->children();
            $value     = $children[0]->content;
            return $value;
        }

        return null;
    }

    /**
    * get the values as an array from the childtags from the first match of the xpath expression
    *
    * @param    string xpath expression
    * @return   array with key->value of subtags
    * @access   public
    */
    function getXpathChildValues($expr)
    {
        $xpath = $this->xmldoc->xpath_new_context();
        $xnode = xpath_eval($xpath, $expr);

        if (isset($xnode->nodeset[0])) {
            foreach ($xnode->nodeset[0]->children() as $child) {
                $children = $child->children();
                $value[$child->{$this->tagname}] = $children[0]->content;
            }
            return $value;
        }

        return null;
    }
}
