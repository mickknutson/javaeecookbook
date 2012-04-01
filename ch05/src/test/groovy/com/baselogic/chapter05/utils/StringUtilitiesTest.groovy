package com.baselogic.chapter05.utils

import groovy.util.GroovyTestCase

class StringUtilitiesTest extends GroovyTestCase {

    StringUtilities stringUtilities = null

    def sampleSets = ["test": "test", "": "", " ": " "] as HashMap

    protected void setUp() {
        super.setUp()
        stringUtilities = new StringUtilities()
    }

    protected void tearDown() {
        super.tearDown()
        stringUtilities = null
    }

    public void testBlankIfNull() {
        String result = StringUtilities.blankIfNull("");
        assertEquals("", result)
    }

    void testSampleDataSet() {
        sampleSets.each() { testSample, expected ->
            String actual = StringUtilities.blankIfNull(testSample);
            println("testSample: [" + testSample +"]")
            println("expected: [" + expected +"]")
            println("actual: [" + actual +"]")
            assertEquals(expected, actual)
        };
    }

    void testBlankIfNullWithBlank() {
        String result = StringUtilities.blankIfNull(" ");
        assertEquals(" ", result)
    }

    void testBlankIfNullWithNull() {
        String result = StringUtilities.blankIfNull(null);
        assertEquals("", result)
    }

    void testBlankIfNullWithValue() {
        String result = StringUtilities.blankIfNull("test");
        assertEquals("test", result)
    }

    void testValueIfBlank() {
        String result = StringUtilities.valueIfBlank("", "value");
        assertEquals("value", result)
    }

    void testValueIfBlankWithSpace() {
        String result = StringUtilities.valueIfBlank(" ", "value");
        assertEquals(" ", result)
    }

    void testValueIfBlankWithNull() {
        String result = StringUtilities.valueIfBlank(null, "value");
        assertEquals("value", result)
    }

    void testValueIfBlankWithValue() {
        String result = StringUtilities.valueIfBlank("test", "value");
        assertEquals("test", result)
    }

    void testNullIfBlank() {
        String result = StringUtilities.nullIfBlank("test");
        assertEquals("test", result)
    }

    void testNullIfBlankWithBlank() {
        String result = StringUtilities.nullIfBlank("");
        assertNull(result)
    }

    void testNullIfBlankWithSpace() {
        String result = StringUtilities.nullIfBlank(" ");
        assertEquals(" ", result)
    }

    void testNullIfBlankWithNull() {
        String result = StringUtilities.nullIfBlank(null);
        assertNull(result)
    }

    def urlsToEncode = ["http://www.google.com/?Test=Verify&This, Equals | That"];
    def urlsToDecode = ["http%3A%2F%2Fwww.google.com%2F%3FTest%3DVerify%26This%2C+Equals+%7C+That"];

    def htmlToEncode = ["<vxml version=\"2.1\"><form id='init'>Test & Verify</form></vxml>"]
    def htmlToDecode = ["&lt;vxml version=&quot;2.1&quot;&gt;&lt;form id='init'&gt;Test &amp; Verify&lt;/form&gt;&lt;/vxml&gt;"]

    void testHtmlEncode() {
        String result = StringUtilities.htmlEncode(htmlToEncode[0]);
        println(result)
        assertEquals(htmlToDecode[0], result)
    }

    /* Does not keep the Map in order
    def requestMap = [ "test1" : "result1", "test2" : "result2", "test3" : "result3"]
    def requestQueryString = "test1=result1&test2=result2&test3=result3&"
    void testGetRequestQueryString() {
        String result = StringUtilities.getRequestQueryString(requestMap);
        println(result)
        assertEquals(requestQueryString, result)
    }*/

    def originalQueryString = "abcdefghijklmnopqrstuvwxyz1234567890!@#%^&*()"
    def resultingQueryString = "abcdefghijklmnopqrstuvwxyz1234567890"

    void testReturnValidAlphaNumericCharacters() {
        String result = StringUtilities.returnValidAlphaNumericCharacters(originalQueryString);
        println(result)
        assertEquals(resultingQueryString, result)
    }

    def originalDigitQueryString = "abcdefghijklmnopqrstuvwxyz1234567890!@#%^&*()"
    def resultingDigitQueryString = "1234567890"

    void testReturnValidDigitCharacters() {
        String result = StringUtilities.returnValidDigitCharacters(originalDigitQueryString);
        println(result)
        assertEquals(resultingDigitQueryString, result)
    }

    void testToBooleanTrue() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("True");
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanTrue2() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("true");
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanT() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("T");
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleant() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("t");
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanFalse() {
        boolean expected = false
        boolean result = StringUtilities.toBoolean("f")
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanFalse2() {
        boolean expected = false
        boolean result = StringUtilities.toBoolean("False")
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanYes() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("Yes")
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanY() {
        boolean expected = true
        boolean result = StringUtilities.toBoolean("y")
        println(result)
        assertEquals(expected, result)
    }

    void testToBooleanNull() {
        boolean expected = false
        boolean result = StringUtilities.toBoolean(null)
        println(result)
        assertEquals(expected, result)
    }


    void testToYesNo() {
        String expected = "Yes"
        String result = StringUtilities.toYesNo(true)
        println(result)
        assertEquals(expected, result)
    }

    void testToYesNo2() {
        String expected = "No"
        String result = StringUtilities.toYesNo(false)
        println(result)
        assertEquals(expected, result)
    }

}