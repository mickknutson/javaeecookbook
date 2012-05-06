package com.baselogic.javaee6;

import java.io.IOException;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpServletResponseWrapper;
import javax.servlet.http.HttpServletRequestWrapper;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

//@WebServlet(name = "mobileDeviceServletFilter", urlPatterns = { "/*" })
public class MobileDeviceServletFilter implements Filter {

    private static final Logger logger = LoggerFactory.getLogger(MobileDeviceServletFilter.class);

    public void init(FilterConfig config) throws ServletException {}

    /**
     *
     * @param request
     * @param response
     * @param chain
     * @throws java.io.IOException
     * @throws javax.servlet.ServletException
     */
    public void doFilter(ServletRequest request,
                         ServletResponse response,
			             FilterChain chain)
    throws IOException, ServletException {

		//if the ServletRequest is an instance of HttpServletRequest
		if(request instanceof HttpServletRequest) {
			//cast the object
			HttpServletRequest httpServletRequest = (HttpServletRequest)request;

            HeaderHttpServletRequestWrapper wrappedRequest = new HeaderHttpServletRequestWrapper(httpServletRequest);

            String userAgent = wrappedRequest.getHeader("User-Agent");
            logger.info(">>>>>>>>>> (Updated)");
            logger.info("User-Agent: {}", userAgent);


			chain.doFilter(wrappedRequest, response);
		} else {
            logger.info(">>>>>>>>>> (Updated)");
            logger.info("Not a HttpServletRequest");
			chain.doFilter(request, response);
		}

    }


    public void destroy() {}
}
