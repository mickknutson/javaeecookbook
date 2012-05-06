package com.baselogic.javaee6;

import com.baselogic.javaee6.annotations.Config;
import com.baselogic.javaee6.domain.Customer;
import com.baselogic.javaee6.service.UserService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.inject.Inject;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

/**
 * Entry Servlet
 *
 * http://localhost:8080/ch03-weld-tomcat7/declarative/entry
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
@WebServlet(
        value = "/declarative/entry",
        name = "entry-servlet"
)
public class EntryServlet extends HttpServlet {

    private static final Logger logger = LoggerFactory.getLogger(EntryServlet.class);

    @Inject
    public UserService userService;

    @Inject @Config
    private String webserviceAddress;

    @Inject @Config
    private Double doubleKeyProperty;


    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.sendRedirect("/ch03/admins/index.xhtml");
    }

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.getWriter().println("<h1>Decorator and Interceptor Test</h1>");
        response.getWriter().println("<b>webserviceAddress: </b>" + webserviceAddress);
        response.getWriter().println("<br /><br /><b>doubleKeyProperty: </b>" + doubleKeyProperty);

        response.getWriter().println("<p><b>findCustomer: </b>" + findCustomer("mickknutson") + "</p>");
    }

    public void init(FilterConfig filterConfig) {
        logger.info("*** init() ***");
        logger.info("webserviceAddress: {}", webserviceAddress);
    }


    public com.baselogic.javaee6.domain.Customer findCustomer(String username) {
        logger.info("_____ EntryServlet.findCustomer({}) _____", username);

        Customer customer = userService.findCustomer(username);
        logger.info("customer: {}", customer);
        return customer;
    }

}
