package com.baselogic.javaee6;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.servlet.*;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpServletResponseWrapper;
import java.io.IOException;
import java.util.*;

//@WebServlet(name = "mobileDeviceServletFilter", urlPatterns = { "/*" })
    /*
    @WebServlet(
    name = "requestProcessorServlet",
    urlPatterns = {"/*"},
    initParams = {
        @InitParam(name = "param1", value = "value1"),
        @InitParam(name = "param2", value = "value2")}
)*/
/**
 * To configure in web.xml:
 * <p/>
 * <filter>
 *  <filter-name>ResponseHeaderFilter</filter-name>
 *  <filter-class>com.comcast.uivr.ResponseHeaderFilter</filter-class>
 *
 *  <init-param>
 *      <param-name>allowableUserAgents</param-name>
 *      <param-value>avaya, voxeo</param-value>
 *  </init-param>
 *
 *  <init-param>
 *      <param-name>allowFromAllUserAgents</param-name>
 *      <param-value>true</param-value>
 *  </init-param>
 *
 *  <init-param>
 *      <param-name>Cache-Control</param-name>
 *      <param-value>max-age=0, no-cache, no-store, must-revalidate</param-value>
 *  </init-param>
 *
 *  <init-param>
 *      <param-name>Expires</param-name>
 *      <param-value>-1</param-value>
 *  </init-param>
 *
 *  <init-param>
 *      <param-name>omit</param-name>
 *      <param-value>etag, bogustag</param-value>
 *  </init-param>
 *
 * </filter>
 *
 * <filter-mapping>
 *  <filter-name>ResponseHeaderFilter</filter-name>
 *  <url-pattern>*.vxml</url-pattern>
 * </filter-mapping>
 */
public class ResponseHeaderFilter implements Filter {

    private static final Logger logger = LoggerFactory.getLogger(ResponseHeaderFilter.class);

    Map<String, String> additionalHeaders = new HashMap<String, String>();

    Set<String> omitHeaders = new TreeSet<String>(String.CASE_INSENSITIVE_ORDER);


    Set<String> allowableUserAgents = new TreeSet<String>(String.CASE_INSENSITIVE_ORDER);

    boolean allowFromAllUserAgents = false;

    public void doFilter(ServletRequest request, ServletResponse res,
                         FilterChain chain)
            throws IOException, ServletException {
        HttpServletResponse response = (HttpServletResponse) res;

        String userAgent = ((HttpServletRequest) request).getHeader("user-agent");

        if (allowFromAllUserAgents
                || (userAgent != null && allowableUserAgents.contains(userAgent))
                ) {
            logger.debug("apply ResponseHeader rules for user agent [{}]", userAgent);
            for (Map.Entry<String, String> entry : additionalHeaders.entrySet()) {
                response.addHeader(entry.getKey(), entry.getValue());
            }

            chain.doFilter(request,
                    new HttpServletResponseWrapper(response) {
                        public void setHeader(String name, String value) {
                            //if (!(name != null && omitHeaders.contains(name.toUpperCase()))) {
                            if (name != null && omitHeaders.contains(name)) {
                                super.setHeader(name, value);
                            }
                        }
                    });
        } else {
            logger.debug("User agent [{}] is not an allowable agent for this filter", userAgent);
            chain.doFilter(request, res);
        }
    }

    /**
     * Called once during start-up
     *
     * @param filterConfig for Filter configuration
     */
    public void init(FilterConfig filterConfig) {

        logger.info("*** ResponseHeaderFilter.init() ***");

        // set the provided HTTP response parameters
        for (Enumeration e = filterConfig.getInitParameterNames(); e.hasMoreElements(); ) {
            String headerName = (String) e.nextElement();
            String headerValue = filterConfig.getInitParameter(headerName);

            // Add the list of allowable user-agents
            // cannot be null: if (headerName != null) {
            if (headerName.equalsIgnoreCase("allowableUserAgents")) {
                // omit
                parseToUpperCaseElements(headerValue, allowableUserAgents);
                logger.debug("allowable user-agent's {}", allowableUserAgents);
            } else if (headerName.equalsIgnoreCase("allowFromAllUserAgents")) {
                allowFromAllUserAgents = Boolean.parseBoolean(headerValue);
                logger.debug("allowFromAllUserAgents {}", allowFromAllUserAgents);
            } else if (headerName.equalsIgnoreCase("omit")) {
                parseToUpperCaseElements(headerValue, omitHeaders);
                logger.debug("Omit headers {}", omitHeaders);
            } else {
                additionalHeaders.put(headerName, headerValue);
                logger.debug("adding header [{}] with value [{}]", headerName, headerValue);
            }
            //}
        }
    }

    protected final void parseToUpperCaseElements(String str, Set<String> elementKeys) {
        String[] words = str.split(",");
        for (String s : words) {
            elementKeys.add(s.trim().toUpperCase());
        }
    }

    public void destroy() {
    }
}