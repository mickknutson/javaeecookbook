package com.baselogic.javaee6.dao;

import com.baselogic.javaee6.domain.Customer;
import com.baselogic.test.CustomerFixture;

import javax.enterprise.context.ApplicationScoped;

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
