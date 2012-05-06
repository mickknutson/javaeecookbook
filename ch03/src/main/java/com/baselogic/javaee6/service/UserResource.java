package com.baselogic.javaee6.service;

import com.baselogic.javaee6.domain.Customer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ejb.Singleton;
import javax.inject.Inject;
import javax.inject.Named;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;

/**
 * UserResource
 *
 * http://localhost:8080/ch03/com.baselogic.javaee6.web/services/customers/mickknutson
 *
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
 *
 */
@Singleton
@Path("customers")
public class UserResource {

    private static final Logger logger = LoggerFactory.getLogger(UserResource.class);

    /*@Inject
    @Named("valuedUserService")
    public UserServiceDecorator valuedUserService;*/

    @Inject
    public UserService userService;

    @GET
    @Path("{username}")
    @Produces({ MediaType.TEXT_XML })
    public Customer findCustomer(@PathParam("username") String username) {
        logger.info("*********************************************************************8");
        logger.info("UserResource.findCustomer(username: {})", username);

        Customer customer = userService.findCustomer(username);
        return customer;
    }

    @GET
    @Path("valued/{username}")
    @Produces({ MediaType.TEXT_XML })
    public Customer findValuedCustomer(@PathParam("username") String username) {

        Customer customer = userService.findCustomer(username);
        return customer;
    }

    @GET
    @Path("json/{username}")
    @Produces({MediaType.APPLICATION_JSON})
    public Customer findCustomerJson(@PathParam("username") String username) {

        Customer customer = userService.findCustomer(username);
        return customer;
    }
}