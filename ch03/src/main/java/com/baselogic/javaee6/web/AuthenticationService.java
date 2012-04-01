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
 *         <a href="http://www.baselogic.com>Blog</a>< /br>
 *         <a href="http://linkedin.com/in/mickknutson>LinkedIN</a>< /br>
 *         <a href="http://twitter.com/mickknutson>Twitter</a>< /br>
 *         <a href="http://www.mickknutson.com>Personal</a>< /br>
 * @since 2011
 *        <i>To change this template use File | Settings | File Templates.</i>
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