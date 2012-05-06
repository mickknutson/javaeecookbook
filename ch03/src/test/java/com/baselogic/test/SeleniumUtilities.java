package com.baselogic.test;

import com.thoughtworks.selenium.DefaultSelenium;

/**
 * [Class_Name]
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
public class SeleniumUtilities {

    /**
     * DefaultSelenium selenium = createSeleniumClient("http://localhost:8888/");
     * @param url
     * @return
     * @throws Exception
     */
    public static DefaultSelenium createSeleniumClient(String url) throws Exception {
        return createSeleniumClient("*firefox", url);
    }

    public static DefaultSelenium createSeleniumClient(String browser, String url) throws Exception {
        System.out.println("----------------------------------------\n\r");
        System.out.println("-- Starting Selenium...");
        return new DefaultSelenium("localhost", 4444, browser, url);
    }

}
