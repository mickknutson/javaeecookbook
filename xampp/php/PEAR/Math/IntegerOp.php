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
// $Id: IntegerOp.php,v 1.1 2003/01/02 01:55:05 jmcastagnetto Exp $
//

include_once 'Math/Integer.php';

/**
 * Class implementing operations on Math_Integer objects. If available it
 * will use the GMP or BCMATH libraries. Will default to the standard PHP
 * integer representation otherwise.
 * 
 * The operations are implemented as static methods of the class.
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.8
 * @access  public
 * @package Math_Integer
 */
class Math_IntegerOp {/*{{{*/

    /**
     * Checks if the given parameter is a Math_Integer object
     *
     * @param object Math_Integer $int1
     * @return boolean TRUE if parameter is an instance of Math_Integer, FALSE otherwise
     * @access public
     */
    function isMath_Integer(&$int) {/*{{{*/
        if (function_exists('is_a')) {
            return is_a($int, 'Math_Integer');
        } else {
            return get_class($int) == 'math_integer' 
                    || is_subclass_of($int, 'math_integer');
        }
    }/*}}}*/

    /**
     * Add two Math_Integer objects: $i1 + $i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &add(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_add($int1->getValue(), $int2->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcadd($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = $int1->getValue() + $int2->getValue(); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Substract two Math_Integer objects: $i1 - $i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &sub(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_sub($int1->getValue(), $int2->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcsub($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = $int1->getValue() - $int2->getValue(); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Multiply two Math_Integer objects: $i1 * $i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &mul(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_mul($int1->getValue(), $int2->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcmul($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = $int1->getValue() * $int2->getValue(); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Divide two Math_Integer objects: $i1 / $i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &div(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_div($int1->getValue(), $int2->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcdiv($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = intval($int1->getValue() / $int2->getValue()); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Calculate the modulus of $i1 and $i2: $i1 % $i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &mod(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_mod($int1->getValue(), $int2->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcmod($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = $int1->getValue() % $int2->getValue(); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Raise $i1 to the $i2 exponent: $i1^$i2
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &pow(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_pow($int1->getValue(), (int) $int2->toString()));
                break;
            case 'bcmath' :
                $tmp = bcpow($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $tmp = pow($int1->getValue(), $int2->getValue()); 
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Compare two Math_Integer objects.
     * if $i1 > $i2, returns +1,
     * if $i1 == $i2, returns +0,
     * if $i1 < $i2, returns -1,
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return mixed and integer on success, PEAR_Error otherwise
     * @access public
     * @see Math_IntegerOp::sign
     */
    function &compare(&$int1, &$int2) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInts($int1, $int2))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $cmp = gmp_cmp($int1->getValue(), $int2->getValue());
                break;
            case 'bcmath' :
                $cmp = bccomp($int1->getValue(), $int2->getValue());
                break;
            case 'std' :
                $cmp = $int1->getValue() - $int2->getValue(); 
                break;
        }/*}}}*/
        return Math_IntegerOp::sign(new Math_Integer($cmp));
    }/*}}}*/

    /**
     * Returns the sign of a Math_Integer number
     * if $i1 > 0, returns +1,
     * if $i1 == 0, returns +0,
     * if $i1 < 0, returns -1,
     *
     * @param object Math_Integer $int1
     * @return mixed and integer on success, PEAR_Error otherwise
     * @access public
     */
    function &sign(&$int1) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInt($int1))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                return gmp_sign($int1->getValue());
                break;
            case 'bcmath' :
            case 'std' :
                $tmp = $int1->getValue();
                if ($tmp > 0) {
                    return 1;
                } elseif ($tmp < 0) {
                    return -1;
                } else { // $tmp == 0
                    return 0;
                }
                break;
        }/*}}}*/
    }/*}}}*/

    /**
     * Returns the negative of a Math_Integer number: -1 * $i1
     *
     * @param object Math_Integer $int1
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &neg(&$int1) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInt($int1))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_neg($int1->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcmul(-1, $int1->getValue());
                break;
            case 'std' :
                $tmp = -1 * $int1->getValue();
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Returns the (integer) square root of a Math_Integer number
     *
     * @param object Math_Integer $int1
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &sqrt(&$int1) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInt($int1))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_sqrt($int1->getValue()));
                break;
            case 'bcmath' :
                $tmp = bcsqrt(-1, $int1->getValue());
                break;
            case 'std' :
                $tmp = intval(sqrt($int1->getValue()));
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Returns the absolute value of a Math_Integer number
     *
     * @param object Math_Integer $int1
     * @return object Math_Integer on success, PEAR_Error otherwise
     * @access public
     */
    function &abs(&$int1) {/*{{{*/
        if (PEAR::isError($err = Math_IntegerOp::_validInt($int1))) {
            return $err;
        }
        switch (MATH_INTLIB) {/*{{{*/
            case 'gmp' :
                $tmp = gmp_strval(gmp_abs($int1->getValue()));
                break;
            case 'bcmath' :
                if ($int1->getValue() < 0) {
                    $tmp = bcmul(-1, $int1->getValue());
                } else {
                    $tmp = $int1->getValue();
                }
                break;
            case 'std' :
                $tmp = abs($int1->getValue());
                break;
        }/*}}}*/
        return new Math_Integer($tmp);
    }/*}}}*/

    /**
     * Checks that the 2 passed objects are valid Math_Integer numbers.
     * The objects must be instances of Math_Integer and have been properly
     * initialized.
     *
     * @param object Math_Integer $int1
     * @param object Math_Integer $int2
     * @return mixed TRUE if both are Math_Integer objects, PEAR_Error otherwise
     * @access private
     */
    function _validInts(&$int1, &$int2) {/*{{{*/
        $err1 = Math_IntegerOp::_validInt($int1);
        $err2 = Math_IntegerOp::_validInt($int2);
        $error = '';
        if (PEAR::isError($err1)) {
            $error .= 'First parameter: '.$err1->getMessage();
        }
        if (PEAR::isError($err2)) {
            $error .= ' Second parameter: '.$err2->getMessage();
        }
        if (!empty($error)) {
            return PEAR::raiseError($error);
        } else {
            return true;
        }
    }/*}}}*/

    /**
     * Checks that the passed object is a valid Math_Integer number.
     * The object must be an instance of Math_Integer and have been properly
     * initialized.
     *
     * @param object Math_Integer $int1
     * @return mixed TRUE if is a Math_Integer object, PEAR_Error otherwise
     * @access private
     */
    function _validInt(&$int1) {/*{{{*/
        $error = '';
        if (!Math_IntegerOp::isMath_Integer($int1)) {
            $error = 'Is not a Math_Integer object.';
        } elseif (!$int1->initialized()) {
            $error = 'Math_Integer object is uninitalized.';
        }
        if (!empty($error)) {
            return PEAR::raiseError($error);
        } else {
            return true;
        }
    }/*}}}*/
}/*}}} end of Math_IntegerOp */

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
?>
