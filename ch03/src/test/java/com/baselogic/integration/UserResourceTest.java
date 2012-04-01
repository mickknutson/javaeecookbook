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
 * [Class_Name]
 * <p/>
 * http://localhost:8080/ch03/com.baselogic.javaee6.web/services/customers/mickknutson
 *
 * @author Mick Knutson
 *         <a href="http://www.baselogic.com>Blog</a>< /br>
 *         <a href="http://linkedin.com/in/mickknutson>LinkedIN</a>< /br>
 *         <a href="http://twitter.com/mickknutson>Twitter</a>< /br>
 *         <a href="http://www.mickknutson.com>Personal</a>< /br>
 * @since 2011
 *        <i>To change this template use File | Settings | File Templates.</i>
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