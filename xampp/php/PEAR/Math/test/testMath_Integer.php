<?php

// $Id: testMath_Integer.php,v 1.2 2003/01/02 01:58:29 jmcastagnetto Exp $
// Example of use of Math_Integer

echo date('\* r')."\n";
echo '* PHP version: '.phpversion()."\n";
echo '* Zend version: '.zend_version()."\n";

// force to use a particular lib
// comment all out to get automatic selection
//define ('MATH_INTLIB', 'gmp');
//define ('MATH_INTLIB', 'bcmath');
//define ('MATH_INTLIB', 'std');

include_once 'Math/IntegerOp.php';
if (MATH_INTLIB == 'gmp' || MATH_INTLIB == 'bcmath') {
    $i1 = new Math_Integer('333333333333333333333333');
    $i2 = new Math_Integer('111111111111111111111111');
} else {
    $i1 = new Math_Integer('33333');
    $i2 = new Math_Integer('11111');
}
$i3 = new Math_Integer(6);

echo '* Using lib: '.MATH_INTLIB."\n";

echo 'i1 = '.$i1->toString()."\n";
echo 'i2 = '.$i2->toString()."\n";
echo 'i3 = '.$i3->toString()."\n";

$res = Math_IntegerOp::add($i1, $i2);
echo 'i1 + i2 = '.$res->toString()."\n";

$res = Math_IntegerOp::sub($i1, $i2);
echo 'i1 - i2 = '.$res->toString()."\n";

$res = Math_IntegerOp::sub($i2, $i1);
echo 'i2 - i1 = '.$res->toString()."\n";

$res = Math_IntegerOp::mul($i1, $i2);
echo 'i1 * i2 = '.$res->toString()."\n";

$res = Math_IntegerOp::div($i1, $i3);
echo 'i1 / i3 = '.$res->toString()."\n";

$res = Math_IntegerOp::mod($i2, $i3);
echo 'i1 % i3 = '.$res->toString()."\n";

$res = Math_IntegerOp::neg($i1);
echo 'neg(i1) = '.$res->toString()."\n";

echo 'sign(neg(i1)) = '.Math_IntegerOp::sign($res)."\n";
echo 'sign(neg(0)) = '.Math_IntegerOp::sign(new Math_Integer(0))."\n";
echo 'sign(i2) = '.Math_IntegerOp::sign($i2)."\n";

echo 'compare(i1, i2) = '.Math_IntegerOp::compare($i1, $i2)."\n";
echo 'compare(i3, i3) = '.Math_IntegerOp::compare($i3, $i3)."\n";
echo 'compare(i2, i1) = '.Math_IntegerOp::compare($i2, $i1)."\n";

$res = Math_IntegerOp::abs(Math_IntegerOp::neg($i2));
echo 'abs(neg(i2)) = '.$res->toString()."\n";

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
?>
