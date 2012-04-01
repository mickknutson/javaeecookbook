package com.baselogic.javaee6;

import com.baselogic.javaee6.configuration.Config;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.inject.Inject;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.annotation.HttpMethodConstraint;
import javax.servlet.annotation.ServletSecurity;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

/**
 * Entry Servlet
 *
 * http://localhost:8080/ch03/declarative/entry
 *
 * @author Mick Knutson
 *         <a href="http://www.baselogic.com>Blog</a>< /br>
 *         <a href="http://linkedin.com/in/mickknutson>LinkedIN</a>< /br>
 *         <a href="http://twitter.com/mickknutson>Twitter</a>< /br>
 *         <a href="http://www.mickknutson.com>Personal</a>< /br>
 * @since 2011
 *        <i>To change this template use File | Settings | File Templates.</i>
 */
@WebServlet(
        value = "/declarative/entry",
        name = "entry-servlet"
)
@ServletSecurity(httpMethodConstraints = {@HttpMethodConstraint("GET"),
        @HttpMethodConstraint(value = "POST", rolesAllowed = {"patients"}),
        @HttpMethodConstraint(value = "TRACE", emptyRoleSemantic = ServletSecurity.EmptyRoleSemantic.DENY)})
public class EntryServlet extends HttpServlet {

    private static final Logger logger = LoggerFactory.getLogger(EntryServlet.class);

    @Inject
    @Config
    private String webserviceAddress;

    @Inject
    @Config
    private Double doubleKeyProperty;

    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.sendRedirect("/ch03/admins/index.xhtml");
    }

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.getWriter().println("<h1>GET UNPROTECTED SERVLET!</h1>");
        response.getWriter().println("<b>webserviceAddress: </b>" + webserviceAddress);
        response.getWriter().println("<br /><br /><b>doubleKeyProperty: </b>" + doubleKeyProperty);
    }

    public void init(FilterConfig filterConfig) {
        logger.info("*** ResponseHeaderFilter.init() ***");
        logger.info("webserviceAddress: {}", webserviceAddress);
    }
}
