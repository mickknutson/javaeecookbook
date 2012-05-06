#!/usr/bin/php -q
<?php
/**
 * Check XML files intended to be used with Translation2
 *
 * @category  Internationalization
 * @package   Translation2
 * @author    Olivier Guilyardi <ylf@xung.org>
 * @copyright 2004-2007 Olivier Guilyardi
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version   CVS: $Id: t2xmlchk.php 245170 2007-10-29 21:10:02Z quipo $
 * @link      http://pear.php.net/package/Translation2
 */
//error_reporting (E_ALL);

if (substr(phpversion(), 0, 1) != '4') {
    exit("Sorry, this script will only run under PHP4 (that is: not PHP5)\n");
}

function print_usage()
{
    echo "t2xmlchk checks and validates XML files intended to be used with \n" . 
         "the Translation2 internationalization package.\n" . 
         "Usage: t2xmlchk <xml filename>\n";
}

require_once 'Translation2/Container/xml.php';
require_once 'XML/DTD/XmlValidator.php';

if (!$xml_file = $argv[1]) {
    echo "ERROR : No xml filename provided\n\n";
    print_usage();
    exit("\n");
}

if (!is_readable($xml_file)) {
    echo "ERROR : No such file : \"$xml_file\"\n\n";
    print_usage();
    exit("\n");
}

$validator =& new XML_DTD_XmlValidator();

$dtd_file = tempnam('/tmp', 't2');
$fp = fopen($dtd_file, 'w');
fwrite($fp, TRANSLATION2_DTD);
fclose($fp);

echo "Performing DTD validation... ";
$test = $validator->isValid($dtd_file, $xml_file);
unlink($dtd_file);
if ($test) {
    echo "OK\n";
} else {
    exit("FAILED : " . $validator->getMessage() . "\n");
}

echo "Unserializing... ";

$keyAttr = array(
    'lang'   => 'id',
    'page'   => 'key',
    'string' => 'key',
    'tr'     => 'lang'
);
$unserializer = &new XML_Unserializer(array('keyAttribute' => $keyAttr));
if (PEAR::isError($status = $unserializer->unserialize($xml_file, true))) {
    exit("FAILED : " .  $status->getMessage() . "\n");
} else {
    echo "OK\n";
}

$data = $unserializer->getUnserializedData();
Translation2_Container_xml::fixEmptySets($data);

// This should be done by XML_DTD :
echo "Checking lang IDs... ";

$known_langs = array();

foreach ($data['languages'] as $lang => $spec) {
    echo "$lang ";
    $known_langs[] = $lang;
    if (isset($spec[0])) {
        exit("FAILED : Found lang duplicate for \"$lang\"\n");
    }
}

echo "OK\n";

echo "Checking string duplicates... ";

foreach ($data['pages'] as $pagename => $pagedata) {
    foreach ($pagedata as $stringname => $stringvalues) {
        if (is_array(array_pop($stringvalues))) {
            exit("FAILED : found duplicate in page \"$pagename\" for string \"stringname\"\n");
        }
    }
}

echo "OK\n";

// This should be done by XML_DTD :
echo "Checking lang IDREFs... ";

foreach ($data['pages'] as $pagename => $pagedata) {
    foreach ($pagedata as $stringname => $stringvalues) {
        foreach ($stringvalues as $lang => $translation) {
            if (!in_array($lang, $known_langs)) {
                exit("FAILED : Unknow lang \"$lang\" in page \"$pagename\" at string \"$stringname\"\n");
            }
        }
    }
}

echo "OK\n";
echo "Checking translation duplicates... ";

foreach ($data['pages'] as $pagename => $pagedata) {
    foreach ($pagedata as $stringname => $stringvalues) {
        foreach ($stringvalues as $lang => $translation) {
            if (is_array($translation)) {
                exit("FAILED : found duplicate in page \"$pagename\" for string \"stringname\" with lang \"$lang\"\n");
            }
        }
    }
}

echo "OK\n";
?>
