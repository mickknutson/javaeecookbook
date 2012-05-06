package com.baselogic.integration;

import com.baselogic.selenium.AbstractSeleniumTestCase;
import org.junit.Before;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import static org.junit.Assert.assertTrue;

/**
 * EntryServletTest
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
public class EntryServletTest extends AbstractSeleniumTestCase {

    final Logger logger = LoggerFactory.getLogger(EntryServletTest.class);

    @Before
    public void beforeTest() throws Exception {
    }

    @Test
    public void testEntryServlet() throws Exception {
        logger.info("----------------------------------------");
        logger.info("/declarative/entry");

        selenium.windowMaximize();

        // base: http://localhost:8181
        selenium.open("/ch03/declarative/entry");
        selenium.waitForPageToLoad("40000");

        selenium.click("link=Patients Area");
        selenium.waitForPageToLoad("30000");

        // Sleep the thread if you want to view the rendered page while testing.
        Thread.sleep(30000);
        assertTrue(true);
    }
}
