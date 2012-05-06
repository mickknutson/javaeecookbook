/**
 * Copyright 2011 Mick Knutson
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0 Unless required
 * by applicable law or agreed to in writing, software distributed
 * under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
package com.baselogic.javaee6;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Enumeration;
import java.util.List;

import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletRequestWrapper;

public class HeaderHttpServletRequestWrapper extends HttpServletRequestWrapper {

    /**
     * Constructor.
     *
     * @param request HttpServletRequest.
     */
    public HeaderHttpServletRequestWrapper(HttpServletRequest request) {
        super(request);
    }

    public String getHeader(String name) {
        //get the request object and cast it
        HttpServletRequest request = (HttpServletRequest) getRequest();

        //if we are looking for the "cookies" request header
        if ("cookie".equals(name)) {
            return getCookieHeaders();
        }

        //otherwise fall through to wrapped request object
        return request.getHeader(name);
    }

    public String getCookieHeaders() {
        //get the request object and cast it
        HttpServletRequest request = (HttpServletRequest) getRequest();

        //loop through the cookies
        Cookie[] cookies = request.getCookies();

        //if cookies are null, then return null
        if (null == cookies) {
            return null;
        } else {
            StringBuilder sb = new StringBuilder();

            sb.append("Cookies: [");
            for (Cookie cookie : cookies) {
                sb.append("[").append(cookie.getName()).append(":");
                sb.append(cookie.getValue()).append("]");
            }
            sb.append("]\n\r");
            return sb.toString();
        }
    }

    public Enumeration<String> getHeaderNames() {
        List<String> list = new ArrayList<String>();

        //loop over request headers from wrapped request object
        HttpServletRequest request = (HttpServletRequest) getRequest();
        Enumeration e = request.getHeaderNames();
        while (e.hasMoreElements()) {
            //add the names of the request headers into the list
            String n = (String) e.nextElement();
            list.add(n);
        }

        list.add("cookie");

        return Collections.enumeration(list);
    }
}