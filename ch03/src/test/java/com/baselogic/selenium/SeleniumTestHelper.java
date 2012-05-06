package com.baselogic.selenium;

import com.baselogic.integration.HomeTest;
import com.thoughtworks.selenium.DefaultSelenium;
import com.thoughtworks.selenium.Selenium;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebDriverBackedSelenium;
import org.openqa.selenium.firefox.FirefoxDriver;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.concurrent.TimeUnit;

public class SeleniumTestHelper {

    private static final Logger logger = LoggerFactory.getLogger(HomeTest.class);

    public static DefaultSelenium init() {
        logger.info("*** Starting selenium client driver ...");

        DefaultSelenium defaultSelenium = new DefaultSelenium(SeleniumConfig.getSeleniumServerHostName(),
                SeleniumConfig.getSeleniumServerPort(),
                "*" + SeleniumConfig.getTargetBrowser(),
                "http://"
                        + SeleniumConfig.getApplicationServerHostName()
                        + ":"
                        + SeleniumConfig.getApplicationServerPort()
                        + "/");
        defaultSelenium.start();
        return defaultSelenium;
    }

    /**
     * @see "http://code.google.com/p/selenium/wiki/ChromeDriver"
     * @return
     */
    public static Selenium initWebDriver() {
        logger.info("*** Starting selenium WebDriver ...");

        WebDriver driver = new FirefoxDriver();
        driver.manage().timeouts().implicitlyWait(30, TimeUnit.SECONDS);
        Selenium selenium = new WebDriverBackedSelenium(driver,
                        "http://"
                        + SeleniumConfig.getApplicationServerHostName()
                        + ":"
                        + SeleniumConfig.getApplicationServerPort()
                        + "/");

        //selenium.start();
        return selenium;
    }

    public static void destroy(Selenium selenium) {
        logger.info("*** Stopping selenium client driver ...");
        selenium.stop();
    }
}
