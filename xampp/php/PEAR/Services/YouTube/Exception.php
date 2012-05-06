<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Exception Class for Services_YouTube
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 */

/**
 * uses PEAR_Exception
 */
require_once 'PEAR/Exception.php';

/**
 * Services_YouTube_Exception
 *
 * This class is used in all place in the package where Exceptions
 * are raised.
 *
 * @package Services_YouTube
 * @author Shin OHNO <ganchiku@gmail.com>
 */
class Services_YouTube_Exception extends PEAR_Exception
{
    /**
     * errorHandlerCallback
     *
     * @param int $code
     * @param string $string
     * @param string $file
     * @param int $line
     * @param array $context
     * @static
     * @access public
     * throw Services_YouTube_Exception
     */
    public static function errorHandlerCallback($code, $string, $file, $line, $context) {
        $e = new self($string, $code);
        $e->line = $line;
        $e->file = $file;
        throw $e;
    }
}
?>
