<?php

/**
 * Image_Color Tests
 *
 * @version $Id: ColorTest.php 267819 2008-10-26 18:21:22Z clockwerx $
 * @copyright 2005
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Color.php';

class ColorTest extends PHPUnit_Framework_TestCase {
    var $color;


    function setUp() {
        $this->color = new Image_Color();
    }
    function tearDown() {
        unset($this->color);
    }

    function testDefaultValues() {
        $this->assertFalse($this->color->_websafeb, 'WebSafe should be false by default.');
        $this->assertEquals(array(), $this->color->color1, 'Color1 should be an empty array by default.');
        $this->assertEquals(array(), $this->color->color2, 'Color2 should be an empty array by default.');
    }

    function testSetWebsafe() {
        $this->color->setWebSafe(true);
        $this->assertTrue($this->color->_websafeb, 'setting true failed.');
        $this->color->setWebSafe(false);
        $this->assertFalse($this->color->_websafeb, 'setting false failed.');
    }

    function testGetGetRange_DefaultParam() {
        $this->color->setColors('#ffffff', '#000000');
        $result = $this->color->getRange();
        $this->assertType('array', $result);
        $this->assertEquals(2, count($result));
    }
    function testGetGetRange_Param5() {
        $this->color->setColors('#ffffff', '#000000');
        $result = $this->color->getRange(5);
        $this->assertType('array', $result);
        $this->assertEquals(5, count($result));
    }

    function testChangeLightness_DefaultParam_SingleColor() {
        $color = array(128,128,128);
        $this->color->setColors(Image_Color::rgb2hex($color), null);
        $this->color->changeLightness();
        $this->assertEquals(array(138,138,138), $this->color->color1);
    }
    function testChangeLightness_NegativeParam_SingleColor() {
        $color = array(128,128,128);
        $this->color->setColors(Image_Color::rgb2hex($color), null);
        $this->color->changeLightness(-5);
        $this->assertEquals(array(123,123,123), $this->color->color1);
    }
    function testChangeLightness_NegativeParam_TwoColors() {
        $color1 = array(128,128,128);
        $color2 = array(64,64,64);
        $this->color->setColors(
            Image_Color::rgb2hex($color1),
            Image_Color::rgb2hex($color2)
        );
        $this->color->changeLightness(-32);
        $this->assertEquals(array(96,96,96), $this->color->color1);
        $this->assertEquals(array(32,32,32), $this->color->color2);
    }


    function testMixColors_FromParam_Orange_Websafe() {
        $red =      '#ff0000';
        $yellow =   '#ffff00';
        $this->color->setWebSafe(true);
        $ret = $this->color->mixColors($red, $yellow);
        $this->assertEquals('FF9900', $ret);
    }
    function testMixColors_FromParam_Orange_NotWebsafe() {
        $red =      '#ff0000';
        $yellow =   '#ffff00';
        $ret = $this->color->mixColors($red, $yellow);
        $this->assertEquals('FF8000', $ret);
    }
    function testMixColors_FromParam_Gray_Websafe() {
        $black =    '#000000';
        $white =    '#ffffff';
        $this->color->setWebSafe(true);
        $ret = $this->color->mixColors($black, $white);
        $this->assertEquals('999999', $ret);
    }
    function testMixColors_FromParam_Gray_NotWebsafe() {
        $black =    '#000000';
        $white =    '#ffffff';
        $ret = $this->color->mixColors($black, $white);
        $this->assertEquals('808080', $ret);
    }
    function testMixColors_FromClass_Orange() {
        $red =      '#ff0000';
        $yellow =   '#ffff00';
        $this->color->setColors($red, $yellow);
        $ret = $this->color->mixColors();
        $this->assertEquals('FF8000', $ret);
    }
    function testMixColors_FromClass_Purple() {
        $red =      '#ff0000';
        $blue =     '#0000ff';
        $this->color->setColors($red, $blue);
        $ret = $this->color->mixColors();
        $this->assertEquals('800080', $ret);
    }


    function testSetColors_Neither() {
        $this->color->setColors(null, null);
        $this->assertEquals(array(), $this->color->color1);
        $this->assertEquals(array(), $this->color->color2);
    }
    function testSetColors_OnlyOne_Hex() {
        $this->color->setColors('ABCDEF', null);
        $this->assertEquals(array(171, 205, 239), $this->color->color1);
        $this->assertEquals(array(), $this->color->color2);
    }
    function testSetColors_Both_Hex() {
        $this->color->setColors('ABCDEF', '012345');
        $this->assertEquals(array(171, 205, 239), $this->color->color1);
        $this->assertEquals(array(  1,  35,  69), $this->color->color2);
    }


    function testHex2rgb_WithPound() {
        $result = Image_Color::hex2rgb('#abcdef');
        $this->assertEquals(array(171,205,239,'hex'=>'#abcdef'), $result);
    }
    function testHex2rgb_WithoutPound() {
        $result = Image_Color::hex2rgb('abcdef');
        $this->assertEquals(array(171,205,239,'hex'=>'abcdef'), $result);
    }


    function testRgb2hex() {
        $result = Image_Color::rgb2hex(array(171,205,239));
        $this->assertEquals('ABCDEF', $result);
    }


    function testGetTextColor_WithParams_OnBlack() {
        $result = Image_Color::getTextColor('#000000', 'light', 'dark');
        $this->assertEquals('light', $result);
    }
    function testGetTextColor_WithParams_OnWhite() {
        $result = Image_Color::getTextColor('#ffffff', 'light', 'dark');
        $this->assertEquals('dark', $result);
    }
    function testGetTextColor_DefaultParams_OnWhite() {
        $result = Image_Color::getTextColor('#ffffff');
        $this->assertEquals('#000000', $result);
    }
    function testGetTextColor_DefaultParams_OnBlack() {
        $result = Image_Color::getTextColor('#000000');
        $this->assertEquals('#FFFFFF', $result);
    }
    function testGetTextColor_DefaultParams_OnRed() {
        $result = Image_Color::getTextColor('#FF0000');
        $this->assertEquals('#FFFFFF', $result);
    }
    function testGetTextColor_DefaultParams_OnBlue() {
        $result = Image_Color::getTextColor('#0000ff');
        $this->assertEquals('#FFFFFF', $result);
    }
    function testGetTextColor_DefaultParams_OnDarkGreen() {
        $result = Image_Color::getTextColor('#006400');
        $this->assertEquals('#FFFFFF', $result);
    }
    function testGetTextColor_DefaultParams_OnLightGreen() {
        $result = Image_Color::getTextColor('90ee90');
        $this->assertEquals('#000000', $result);
    }


    function testColor2RGB_Hex() {
        $result = Image_Color::color2RGB('#00ff00');
        $this->assertEquals(array(0,255,0,'hex'=>'#00ff00'), $result);
    }
    function testColor2RGB_Named() {
        $result = Image_Color::color2RGB('red');
        $this->assertEquals(array(255,0,0), $result);
    }


    function testNamedColor2RGB_Valid() {
        $result = Image_Color::namedColor2RGB('orange');
        $this->assertEquals(array(255,165,0), $result);
    }
    function testNamedColor2RGB_InvalidReturnsBlack() {
        $result = Image_Color::namedColor2RGB('NOT A REAL COLOR');
        $this->assertEquals(array(0,0,0), $result);
    }


    function testPercentageColor2RGB_100s() {
        $result = Image_Color::percentageColor2RGB("100%,100%,100%");
        $this->assertEquals(array(255,255,255), $result);
    }
    function testPercentageColor2RGB_0s() {
        $result = Image_Color::percentageColor2RGB("0%,0%,0%");
        $this->assertEquals(array(0,0,0), $result);
    }
    function testPercentageColor2RGB_2digits() {
        $result = Image_Color::percentageColor2RGB("10%,50%,90%");
        $this->assertEquals(array(26,127,229), $result);
    }


    function testMakeWebSafe_00() {
        $expected = 0x00;
        $param = -1;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 0;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 25;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
    function testMakeWebSafe_33() {
        $expected = 0x33;
        $param = 26;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 76;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
    function testMakeWebSafe_66() {
        $expected = 0x66;
        $param = 77;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 127;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
    function testMakeWebSafe_99() {
        $expected = 0x99;
        $param = 128;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 178;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
    function testMakeWebSafe_cc() {
        $expected = 0xcc;
        $param = 179;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 229;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
    function testMakeWebSafe_ff() {
        $expected = 0xff;
        $param = 230;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 255;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
        $param = 257;
        $actual = _makeWebSafe($param);
        $this->assertEquals($expected, $actual, "test return");
        $this->assertEquals($expected, $param, 'test param passed by ref');
    }
}

?>
