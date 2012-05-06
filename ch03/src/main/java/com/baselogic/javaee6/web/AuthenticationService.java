package com.baselogic.javaee6.web;

import javax.faces.bean.RequestScoped;
import javax.faces.context.ExternalContext;
import javax.faces.context.FacesContext;
import javax.inject.Named;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import java.security.Principal;

/**
 * [Class_Name]
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
@Named
@RequestScoped
public class AuthenticationService {
    private String username;
    private String password;

    /**
     * Performs authentication via HttpServletRequest API
     */
    public boolean authenticate() {
        if (!isAuthenticated()) {
            try {
                getRequest().login(username, password);
            } catch (ServletException e) {
            }
        }
        return isAuthenticated();
    }

    /**
     * Logs out using HttpServletRequest API
     */
    public void logout() throws ServletException {
        if (isAuthenticated())
            getRequest().logout();
        //return null;
    }

    public boolean isAuthenticated() {
        return getRequest().getUserPrincipal() != null;
    }

    public boolean isUserInRole(String role) {
        return getRequest().isUserInRole(role);
    }

    public Principal getPrincipal() {
        return getRequest().getUserPrincipal();
    }

    private HttpServletRequest getRequest() {
        FacesContext facesContext = FacesContext.getCurrentInstance();
        ExternalContext externalContext = facesContext.
                getExternalContext();
        Object request = externalContext.getRequest();
        return request instanceof HttpServletRequest ?
                (HttpServletRequest) request : null;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

}