package com.baselogic.integration;

import com.baselogic.javaee6.domain.Customer;
import com.baselogic.javaee6.service.UserResource;
import com.sun.jersey.api.client.Client;
import com.sun.jersey.api.client.ClientResponse;
import com.sun.jersey.api.client.WebResource;
import com.sun.jersey.api.client.config.ClientConfig;
import com.sun.jersey.api.client.config.DefaultClientConfig;
import org.junit.After;
import org.junit.Before;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.ws.rs.core.UriBuilder;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.Matchers.lessThan;
import static org.hamcrest.Matchers.greaterThanOrEqualTo;
import static org.junit.Assert.assertThat;
import static org.hamcrest.Matchers.not;
import static org.hamcrest.Matchers.nullValue;

/**
 * UserResourceTest
 *
 * For Selenium Tests:
 * http://localhost:8080/ch03/com.baselogic.javaee6.web/services/customers/mickknutson
 *
 * http://localhost:8080/ch03/com.baselogic.javaee6.web/services/customers/valued/mickknutson
 *
 * For Web Page using Tomcat 7:
 * http://localhost:8080/ch03/services/customers/mickknutson
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
public class UserResourceTest {

    final Logger logger = LoggerFactory.getLogger(UserResourceTest.class);

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    public UserResource userResource;

    ClientConfig config = new DefaultClientConfig();

    Client client = Client.create(config);

    WebResource webResource;

    //-----------------------------------------------------------------------//
    // Lifecycle Methods
    //-----------------------------------------------------------------------//
    @Before
    public void before() throws Exception {
        userResource = new UserResource();

        webResource = client.resource(
                UriBuilder.fromUri(
                        "http://localhost:8888/ch03/services")
                        .build()
        );
    }

    @After
    public void after() throws Exception {
        //
    }

    //-----------------------------------------------------------------------//
    // Unit Tests
    //-----------------------------------------------------------------------//

    @Test
    public void test_Read_Client_Customer_Object() throws Exception {

        // Get Customer Object
        ClientResponse response = webResource.path("customers/mickknutson")
                .get(ClientResponse.class);
        int status = response.getStatus();
        assertThat(status, is(greaterThanOrEqualTo(200)));
        assertThat(status, is(lessThan(400)));

        Customer result = response.getEntity(Customer.class);
        assertThat(result.getFirstName(), is(not(nullValue())));

        logger.info(result.toString());
    }

    @Test
    public void test_Read_Client_Customer_Xml() throws Exception {

        // Get Customer XML
        String result = webResource.path("customers/mickknutson").get(String.class);
        assertThat(result, is(not(nullValue())));

        logger.info(result);
    }

    @Test
    public void test_Read_Client_Customer_Json() throws Exception {

        String result = webResource.path("customers/json/mickknutson").get(String.class);
        assertThat(result, is(not(nullValue())));

        logger.info(result);
    }

}