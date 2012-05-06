package com.baselogic.javaee6.web;

import javax.annotation.security.DeclareRoles;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;

/**
 * Login Servlet
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
        value = "/loginServlet",
        name = "LoginServlet"
)
@DeclareRoles("javaee6user")
public class LoginServlet extends HttpServlet {
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        processRequest(request, response);
    }

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        processRequest(request, response);
    }

    /**
     * Processes requests for both HTTP GET and POST methods.
     *
     * @param request  servlet request
     * @param response servlet response
     */
    protected void processRequest(HttpServletRequest request,
                                  HttpServletResponse response)
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();
        try {
            String userName = request.getParameter("txtUserName");
            String password = request.getParameter("txtPassword");

            out.println("Before Login" + "<br><br>");
            out.println("IsUserInRole?.."
                    + request.isUserInRole("javaee6user") + "<br>");
            out.println("getRemoteUser?.." + request.getRemoteUser() + "<br>");
            out.println("getUserPrincipal?.."
                    + request.getUserPrincipal() + "<br>");
            out.println("getAuthType?.." + request.getAuthType() + "<br><br>");

            try {
                request.login(userName, password);
            } catch (ServletException ex) {
                out.println("Login Failed with a ServletException.."
                        + ex.getMessage());
                return;
            }
            out.println("After Login..." + "<br><br>");
            out.println("IsUserInRole?.."
                    + request.isUserInRole("javaee6user") + "<br>");
            out.println("getRemoteUser?.." + request.getRemoteUser() + "<br>");
            out.println("getUserPrincipal?.."
                    + request.getUserPrincipal() + "<br>");
            out.println("getAuthType?.." + request.getAuthType() + "<br><br>");

            request.logout();
            out.println("After Logout..." + "<br><br>");
            out.println("IsUserInRole?.."
                    + request.isUserInRole("javaee6user") + "<br>");
            out.println("getRemoteUser?.." + request.getRemoteUser() + "<br>");
            out.println("getUserPrincipal?.."
                    + request.getUserPrincipal() + "<br>");
            out.println("getAuthType?.." + request.getAuthType() + "<br>");
        } finally {
            out.close();
        }
    }

}
