package com.baselogic.javaee6.service;

import com.baselogic.javaee6.configuration.Config;
import com.baselogic.javaee6.dao.UserDao;
import com.baselogic.javaee6.domain.Customer;

import javax.decorator.Decorator;
import javax.decorator.Delegate;
import javax.ejb.Stateless;
import javax.enterprise.inject.Instance;
import javax.inject.Inject;
import javax.inject.Named;

/**
 * UserServiceDecorator
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
//@Named("valuedUserService")
@Decorator
public class UserServiceDecorator implements UserService {

    @Inject
    @Delegate
    private UserService userService;

    @Inject
    @Config
    private String valuedCustomerDiscount;

    private Instance<UserService> userService2;

    public Customer findCustomer(String username) {

        // Delegate the request to the target object:
        Customer valuedCustomer = userService.findCustomer(username);

        // Perform additional logic for this decoration.
        //valuedCustomer.setDiscount(valuedCustomerDiscount);

        return valuedCustomer;
    }
}
