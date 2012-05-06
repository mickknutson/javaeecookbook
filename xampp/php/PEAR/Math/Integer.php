<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jesus M. Castagnetto <jmcastagnetto@php.net>                |
// +----------------------------------------------------------------------+
//
// $Id: Integer.php,v 1.1 2003/01/02 01:55:05 jmcastagnetto Exp $
//

include_once 'PEAR.php';

/** 
 * The maximum integer we will accept if using the standard signed long
 */
define ('MATH_MAXINT', 2147483647);

// allow for user override of the integer library to use
if (!defined('MATH_INTLIB')) {
    if (extension_loaded('gmp')) {
        define ('MATH_INTLIB', 'gmp');
    } elseif (extension_loaded('bcmath')) {
        define ('MATH_INTLIB', 'bcmath');
        bcscale(0);
    } else {
        define ('MATH_INTLIB', 'std');
    }
}

/**
 * Class to represent integers. If the GMP or BCMATH libraries are present the
 * integer can be of arbitrary size, otherwise the internal PHP integer
 * representation will be use (signed long). If the latter case, you cannot
 * create Math_Integer objects bigger than MATH_MAXINT.
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.8
 * @access  public
 * @package Math_Integer
 */
class Math_Integer {/*{{{*/
    /**
     * If the GMP library is present, this variable will contain the resource
     * handle created using gmp_init(). If only the BCMATH library is present
     * it will contain the string representation of the integer. If neither
     * GMP or BCMATH are present, it will contain the actual integer.
     * 
     * @access private
     * @var mixed
     */
	var $_value = null;

    /**
     * Class constructor, accepts an optional number or string representing the integer.
     *
     * @param optional mixed $num an integer or its string representation
     * @return Math_Integer object
     * @access public
     */
    function Math_Integer($num=null) {/*{{{*/
        if (!is_null($num)) {
            $this->setValue($num);
        }
    }/*}}}*/
 
    /**
     * Method to set the value for the object
     * @param optional mixed $num an integer or its string representation
     * @return mixed TRUE on success, a PEAR_Error otherwise
     * @access public
     */
    function setValue($num) {/*{{{*/
        if (!is_scalar($num)) {
            $this->_value = null;
            return PEAR::raiseError('Parameter is not a number');
        }
        switch (MATH_INTLIB) {
            case 'gmp' :
                $this->_value = gmp_init(strval($num));
                return true;
                break;
            case 'bcmath' :
                $this->_value = $num;
                return true;
                break;
            case 'std' :
            default :
                if ($num > MATH_MAXINT) {
                    $this->_value = null;
                    return PEAR::raiseError('Cannot initialize, number > MATH_MAXINT '.
                                'and no gmp or bcmath extensions detected');
                } else {
                    $this->_value = intval($num);
                    return true;
                }
                break;
        }
    }/*}}}*/

    /**
     * Returns the content of $this->_value
     *
     * @return mixed the value on success, a PEAR_Error otherwise
     * @access public
     */
    function getValue() {/*{{{*/
        if (!$this->initialized()) {
            return PEAR::raiseError('Math_Integer object not initialized');
        }
        return $this->_value;
    }/*}}}*/

    /**
     * Returns a string representation of the value
     *
     * @return mixed a string on success, a PEAR_Error otherwise
     * @access public
     */
    function toString() {/*{{{*/
        if (!$this->initialized()) {
            return PEAR::raiseError('Math_Integer object not initialized');
        }
        if (MATH_INTLIB == 'gmp') {
            return gmp_strval($this->_value);
        } else { // for 'std' and 'bcmath'
            return (string) $this->_value;
        }
    }/*}}}*/

    /**
     * Checks if the object has been succesfully initialized
     *
     * @return boolean TRUE if initialized, FALSE otherwise
     * @access public
     */
    function initialized() {/*{{{*/
        return !is_null($this->_value);
    }/*}}}*/
}/*}}} end of Math_Integer */

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
?>
