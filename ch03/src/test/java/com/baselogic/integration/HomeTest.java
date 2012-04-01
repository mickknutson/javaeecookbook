package com.baselogic.integration;

import com.baselogic.selenium.AbstractSeleniumTestCase;
import com.baselogic.selenium.SeleniumTestHelper;
import org.junit.Before;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import static org.junit.Assert.assertTrue;

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
public class HomeTest extends AbstractSeleniumTestCase {

    final Logger logger = LoggerFactory.getLogger(HomeTest.class);

    @Before
    public void beforeTest() throws Exception {
    }

    @Test
    public void testHomePage() throws Exception {
        logger.info("----------------------------------------");
        logger.info("testHomePage()");

        selenium.windowMaximize();

        // base: http://localhost:8181
        selenium.open("/ch03/");
        selenium.waitForPageToLoad("40000");

        selenium.click("link=Patients Area");
        selenium.waitForPageToLoad("30000");

        // Sleep the thread if you want to view the rendered page while testing.
        Thread.sleep(30000);
        assertTrue(true);
    }







    /**
     * http://localhost:8080/ch03/services/customers/mickknutson
     * http://localhost:8888/ch03/services/customers/mickknutson
     * @throws Exception
     */
    //@Test
    public void testUserResource() throws Exception {
        logger.info("----------------------------------------");
        logger.info("testUserResource()");

        selenium.windowMaximize();

        selenium.open("/ch03/services/customers/mickknutson");
        selenium.waitForPageToLoad("30000");
        assertTrue(selenium.isTextPresent("<postCode>94114</postCode>"));

        // Sleep the thread if you want to view the rendered page while testing.
        Thread.sleep(20000);
        assertTrue(true);
    }
}
