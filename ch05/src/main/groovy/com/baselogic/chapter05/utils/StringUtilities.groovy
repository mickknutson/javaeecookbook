package com.baselogic.chapter05.utils

import org.apache.commons.lang.StringEscapeUtils

class StringUtilities {

    public static String blankIfNull(String s) {
        return (s == null) ? "" : s;
    }

    public static String valueIfBlank(String target, String value) {
        return (blankIfNull(target) == "") ? value : target
    }

    public static String nullIfBlank(String value) {
        return valueIfBlank(value, null);
    }

    public static boolean toBoolean(String value) {
        return ("True".equalsIgnoreCase(value) || "T".equalsIgnoreCase(value)
            || "Yes".equalsIgnoreCase(value) || "Y".equalsIgnoreCase(value))
    }

    public static String toYesNo(boolean value) {
        return (value? 'Yes': 'No')
    }

    public static String htmlEncode(String s) {
        return org.apache.commons.lang.StringEscapeUtils.escapeHtml(s)
    }

    /**
     * Map map = javax.servlet.ServletRequest.getParameterMap()
     * String s = getRequestQueryString(map)
     *On
     * or
     * String s = getRequestQueryString(javax.servlet.ServletRequest.getParameterMap())
     */
    static String getRequestQueryString(Map<String, String> parameters) {
        StringBuilder sb = new StringBuilder();
        parameters.each() {key, value -> sb.append("${key}=${value}&") };
        return sb.toString()
    }

    public static String returnValidAlphaNumericCharacters(String s) {
        return s.replaceAll(/([^0-9A-Za-z]+)/, '')
    }
    public static String returnValidDigitCharacters(String s) {
        return s.replaceAll(/([^0-9]+)/, '')
    }
}