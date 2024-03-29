package com.baselogic.javaee6.service;

import com.baselogic.javaee6.annotations.Debugable;
import com.baselogic.javaee6.annotations.Interceptable;
import com.baselogic.javaee6.annotations.Timer;
import com.baselogic.javaee6.domain.Customer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ejb.Stateless;

/**
 * UserServiceImpl
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
@Stateless
public class UserServiceImpl implements UserService{

    private static final Logger logger = LoggerFactory.getLogger(UserServiceImpl.class);

    @Override
    @Interceptable @Debugable @Timer
    public Customer findCustomer(String username) {
        logger.info("UserServiceImpl.findCustomer({})", username);

        Customer customer = new Customer();
        customer.setUsername(username);
        return customer;
    }
}