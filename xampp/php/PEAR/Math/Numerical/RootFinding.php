<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * File contains Math_Numerical_RootFinding base class.
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * BSD License
 *
 * Copyright (c) 2004-2006 Firman Wandayandi
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 * 3. Neither the name of Firman Wandayandi nor the names of
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Math
 * @package Math_Numerical_RootFinding
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2004-2006 Firman Wandayandi
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version CVS: $Id: RootFinding.php,v 1.4 2006/01/30 18:22:02 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Require PEAR for handling errors.
 */
require_once 'PEAR.php';

// }}}
// {{{ Global variables

/**
 * Method driver aliases in order to create the prety file names,
 * also for insensitive-case of method name calls.
 *
 * @global array $GLOBALS['_Math_Numerical_RootFinding_drivers']
 * @_Math_Numerical_RootFinding_drivers
 */
$GLOBALS['_Math_Numerical_RootFinding_drivers'] = array(
    'bisection'         => 'Bisection',
    'falseposition'     => 'FalsePosition',
    'fixedpoint'        => 'FixedPoint',
    'newtonraphson'     => 'NewtonRaphson',
    'newtonraphson2'    => 'NewtonRaphson2',
    'ralstonrabinowitz' => 'RalstonRabinowitz',
    'secant'            => 'Secant'
);

// }}}
// {{{ Class: Math_Numerical_RootFinding

/**
 * Math_Numerical_RootFinding base class.
 *
 * This class intended for build API structure and abstract class members.
 *
 * @category Math
 * @package Math_Numerical_RootFinding
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2004-2006 Firman Wandayandi
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version Release: 1.0.0
 */
class Math_Numerical_RootFinding
{
    // {{{ factory()

    /**
     * Create new instance of RootFinding method class.
     *
     * @param string $method Method name.
     * @param array $options (optional) Options (options is available in
     *                                  specified method class).
     *
     * @return object New method's class on success or PEAR_Error on failure.
     * @access public
     * @static
     */
    function &factory($method, $options = null)
    {
        $method = strtolower(trim($method));
        if (!isset($GLOBALS['_Math_Numerical_RootFinding_drivers'][$method])) {
            return PEAR::raiseError('Driver file not found for ' .
                                    '\'' . $method . '\' method');
        }

        $method = $GLOBALS['_Math_Numerical_RootFinding_drivers'][$method];
        $filename = dirname(__FILE__) . '/RootFinding/' . $method . '.php';

        if (!file_exists($filename)) {
            return PEAR::raiseError('Driver file not found for ' .
                                    '\'' . $method . '\' method');
        }

        include_once $filename;
        $classname = 'Math_Numerical_RootFinding_' . $method;
        if (!class_exists($classname)) {
            return PEAR::raiseError('Undefined class \'' . $classname . '\'');
        }

        $obj =& new $classname;
        if (!is_object($obj) || !is_a($obj, $classname)) {
            return PEAR::raiseError('Failed creating object from class '.
                                    '\'' . $classname . '\'');
        }

        if ($options !== null) {
            $err = $obj->set($options);
            if (PEAR::isError($err)) {
                return $err;
            }
        }

        return $obj;
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
