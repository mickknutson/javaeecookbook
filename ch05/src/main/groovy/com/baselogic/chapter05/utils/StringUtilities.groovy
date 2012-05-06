package com.baselogic.chapter05.utils

import org.apache.commons.lang.StringEscapeUtils

/**
 * StringUtilities
 *
 * @author Mick Knutson
 * @see <a href="http://www.baselogic.com">Blog: http://baselogic.com</a>
 * @see <a href="http://linkedin.com/in/mickknutson">LinkedIN: http://linkedin.com/in/mickknutson</a>
 * @see <a href="http://twitter.com/mickknutson">Twitter: http://twitter.com/mickknutson</a>
 * @see <a href="http://github.com/mickknutson">Git hub: http://github.com/mickknutson</a>
 *
 * @see <a href="http://www.packtpub.com/java-ee6-securing-tuning-extending-enterprise-applications-cookbook/book">JavaEE 6 Cookbook Packt</a>
 * @see <a href="http://www.amazon.com/Cookbook-securing-extending-enterprise-applications/dp/1849683166">JavaEE 6 Cookbook Amazon</a>
 *
 * @since 2012
 */
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