package com.baselogic.javaee6.dao;

import com.baselogic.javaee6.domain.Customer;
import com.baselogic.test.CustomerFixture;

import javax.enterprise.context.ApplicationScoped;
import javax.inject.Named;

/**
 * UserDaoImpl
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
@ApplicationScoped
public class UserDaoImpl implements UserDao {

    public UserDaoImpl() {}

    public Customer findCustomer(String username) {
        return CustomerFixture.createSingleCustomer();
    }

    public Customer createCustomer(String username,
                                   String firstName,
                                   String lastName) {
        return new Customer(username, firstName, lastName);
    }
}
