package com.baselogic.integration;

import com.baselogic.javaee6.dao.UserDao;
import com.baselogic.javaee6.domain.Customer;
import com.baselogic.javaee6.service.UserService;
import com.baselogic.test.CustomerFixture;
import org.junit.After;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.Matchers.not;
import static org.hamcrest.Matchers.nullValue;
import static org.junit.Assert.assertThat;
import static org.mockito.Mockito.*;

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
public class UserServiceTest {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    public UserService userService;

    public UserDao userDaoMock;

    //-----------------------------------------------------------------------//
    // Lifecycle Methods
    //-----------------------------------------------------------------------//
    @Before
    public void before() throws Exception {
        userService = new UserService();

        // Create Mock UserDao
        userDaoMock = mock(UserDao.class);

        // set userDaoMock into UserService.
        this.userService.userDao = userDaoMock;
    }

    @After
    public void after() throws Exception {
        //
    }

    //-----------------------------------------------------------------------//
    // Unit Tests
    //-----------------------------------------------------------------------//

    /**
     * http://localhost:8080/ch03/services/customers/mickknutson
     * http://localhost:8888/ch03/services/customers/mickknutson
     * @throws Exception
     */
    @Test
    public void test_FindMock_Customer() throws Exception {

        // Create test data for Mock to return
        Customer customer = CustomerFixture.createSingleCustomer();

        // Set conditions for Mock object.
        when(userDaoMock.findCustomer(any(String.class)))
                .thenReturn(customer);

        // Execute service.
        Customer result = userService.findCustomer("mickknutson");

        // Assert result.
        assertThat(result.getFirstName(), is(not(nullValue())));

        // Assert Mock executed.
        verify(userDaoMock);

    }
}