package com.baselogic.javaee6.service;

import com.baselogic.javaee6.dao.UserDao;
import com.baselogic.javaee6.domain.Customer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ejb.Singleton;
import javax.inject.Inject;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;

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
@Singleton
@Path("customers")
public class UserResource {

    private static final Logger logger = LoggerFactory.getLogger(UserResource.class);

    @Inject
    public UserDao userDao;

    @GET
    @Path("{username}")
    @Produces({ MediaType.TEXT_XML })
    public Customer findCustomer(@PathParam("username") String username) {

        Customer customer = userDao.findCustomer(username);
        return customer;
    }

    @GET
    @Path("json/{username}")
    @Produces({MediaType.APPLICATION_JSON})
    public Customer findCustomerJson(@PathParam("username") String username) {

        Customer customer = userDao.findCustomer(username);
        return customer;
    }
}