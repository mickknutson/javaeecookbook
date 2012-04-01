package com.baselogic.selenium;

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
import com.thoughtworks.selenium.Selenium;
import org.junit.AfterClass;
import org.junit.BeforeClass;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public abstract class AbstractSeleniumTestCase {

    private static final Logger logger = LoggerFactory.getLogger(AbstractSeleniumTestCase.class);

    protected static Selenium selenium;

    @BeforeClass
    public static void beforeClass() throws Exception{
        selenium = SeleniumTestHelper.init();
    }

    @AfterClass
    public static void destroy(){
        SeleniumTestHelper.destroy(selenium);
    }

}
