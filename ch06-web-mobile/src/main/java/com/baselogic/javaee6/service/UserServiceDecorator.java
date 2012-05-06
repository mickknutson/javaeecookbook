package com.baselogic.javaee6.service;

import com.baselogic.javaee6.annotations.Config;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.decorator.Decorator;
import javax.decorator.Delegate;
import javax.enterprise.inject.Any;
import javax.inject.Inject;

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
@Decorator
public class UserServiceDecorator implements UserService {

    private static final Logger logger = LoggerFactory.getLogger(UserServiceDecorator.class);

    @Inject @Delegate @Any
    private UserService userService;

    @Inject @Config
    private Double valuedCustomerDiscount;

    @Override
    public com.baselogic.javaee6.domain.Customer findCustomer(String username) {
        logger.info("UserServiceDecorator.findCustomer({})", username);

        // Delegate the request to the target object:
        com.baselogic.javaee6.domain.Customer valuedCustomer = userService.findCustomer(username);
        logger.info("valuedCustomer pre-discount: {}", valuedCustomer.getDiscount());

        // Perform additional logic for this decoration.
        valuedCustomer.setDiscount(valuedCustomerDiscount);

        return valuedCustomer;
    }
}
