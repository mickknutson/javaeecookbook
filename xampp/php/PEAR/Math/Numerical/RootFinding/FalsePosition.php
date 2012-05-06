<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * Driver file contains Math_Numerical_RootFinding_Bisection class to provide
 * False Position/Regula Falsi method root finding calculation.
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
 * @subpackage Methods
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2004-2006 Firman Wandayandi
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version CVS: $Id: FalsePosition.php,v 1.1 2006/01/30 18:42:00 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load Math_Numerical_RootFinding_Common as base class.
 */
require_once 'Math/Numerical/RootFinding/Common.php';

// }}}
// {{{ Class: Math_Numerical_RootFinding_FalsePosition

/**
 * False Position/Regula Falsi method class.
 *
 * @category Math
 * @package Math_Numerical_RootFinding
 * @subpackage Methods
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2004-2006 Firman Wandayandi
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version Release: 1.0.0
 */
class Math_Numerical_RootFinding_FalsePosition
extends Math_Numerical_RootFinding_Common
{
    // {{{ Constructor

    /**
     * Constructor.
     *
     * @param array $options (optional) Options.
     *
     * @access public
     * @see Math_Numerical_RootFinding_Common::Math_Numerical_RootFinding_Common()
     */
    function Math_Numerical_RootFinding_FalsePosition($options = null)
    {
        parent::Math_Numerical_RootFinding_Common($options);
    }

    // }}}
    // {{{ infoCompute()

    /**
     * Print out parameters description for compute() function.
     *
     * @access public
     */
    function infoCompute()
    {
        print "<h2>False Position::compute()</h2>\n" .

              "<em>float</em> | <em>PEAR_Error</em> ".
              "<strong>compute</strong>(<u>\$fxFunction</u>, <u>\$xL</u>, ".
              "<u>\$xU</u>)<br />\n" .

              "<h3>Description</h3>\n".

              "<em>callback</em> <u>\$fxFunction</u> Callback f(x) equation ".
              "function or object/method tuple.<br>\n" .

              "<em>float</em> <u>\$xL</u> Lower guess.<br>\n" .

              "<em>float</em> <u>\$xU</u> Upper guess.<br>\n";
    }

    // }}}
    // {{{ compute()

    /**
     * False Position/Regula Falsi method.
     *
     * @param callback $fxFunction Callback f(x) equation function or
     *                             object/method tuple.
     * @param float $xL Lower guess.
     * @param float $xU Upper guess.
     *
     * @return float|PEAR_Error root value on success or PEAR_Error on failure.
     * @access public
     * @see Math_Numerical_RootFinding_Common::validateEqFunction()
     * @see Math_Numerical_RootFinding_Common::getEqResult()
     * @see Math_Numerical_RootFinding_Bisection::compute()
     */
    function compute($fxFunction, $xL, $xU)
    {
        // Validate f(x) equation function.
        $err = Math_Numerical_RootFinding_Common::validateEqFunction($fxFunction);
        if (PEAR::isError($err)) {
            return $err;
        }

        // Sets maximum iteration and tolerance from options.
        $maxIteration = $this->options['max_iteration'];
        $errTolerance = $this->options['err_tolerance'];

        // Calculate first approximation $xR (False Position's formula).
        $fxL = Math_Numerical_RootFinding_Common::getEqResult($fxFunction, $xL);
        $fxU = Math_Numerical_RootFinding_Common::getEqResult($fxFunction, $xU);
        if ($fxL - $fxU == 0) {
            return PEAR::raiseError('Iteration skipped, division by zero');
        }
        $xR = $xU - (($fxU * ($xL - $xU)) / ($fxL - $fxU));

        // Sets variable for saving errors during iteration, for divergent
        // detection.
        $epsErrors = array();

        for ($i = 0; $i < $maxIteration; $i++) {
            // Calculate f(xr), where: xr = $xR
            $fxR = Math_Numerical_RootFinding_Common::getEqResult($fxFunction, $xR);

            if ($fxL * $fxR < 0) { // Root is at first subinterval.
                $xU = $xR;
            } elseif ($fxL * $fxR > 0) { // Root is at second subinterval.
                $xL = $xR;
            } elseif ($fxL * $fxR == 0) { // $xR is the exact root.
                $this->root = $xR;
                break;
            }

            // Avoid division by zero.
            if ($fxL - $fxU == 0) {
                return PEAR::raiseError('Iteration skipped, division by zero');
            }

            // Compute new approximation.
            $xN = $xU - (($fxU * ($xL - $xU)) / ($fxL - $fxU));

            // Compute approximation error.
            $this->epsError = abs(($xN - $xR) / $xN);
            $epsErrors[] = $this->epsError;

            // Detect for divergent rows.
            if ($this->isDivergentRows($epsErrors) &&
                $this->options['divergent_skip'])
            {
                return PEAR::raiseError('Iteration skipped, divergent rows detected');
                break;
            }

            // Check for error tolerance, if lower than or equal with
            // $errTolerance it is the root.
            if ($this->epsError <= $errTolerance) {
                $this->root = $xN;
                break;
            }

            // Calculate f(xl), where xl = $xL.
            $fxL = Math_Numerical_RootFinding_Common::getEqResult($fxFunction, $xL);
            // Calculate f(xu), where xu = $xU.
            $fxU = Math_Numerical_RootFinding_Common::getEqResult($fxFunction, $xU);

            // Switch xn -> xr, where xn = $xN and xr = $xR.
            $xR = $xN;
        }
        $this->iterationCount = $i;
        return $this->root;
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
