package com.baselogic.test;

import com.thoughtworks.selenium.DefaultSelenium;

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
