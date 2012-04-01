package com.baselogic.selenium;

public class SeleniumConfig {
    private static String seleniumServerHostName = "localhost";
    private static int seleniumServerPort = 4444;
    private static String applicationServerHostName = "localhost";
    private static int applicationServerPort = 8888;

    //You might have to use full path e.g on Fedora 12: 'firefox /usr/lib/firefox-3.5.4/firefox'
    //See http://seleniumhq.org/docs/05_selenium_rc.html#firefox-on-linux
    //for mac osx, use 'safari'
    private static String targetBrowser = "firefox";
    //For linux: private static String targetBrowser = "firefox /usr/lib/firefox-3.5.4/firefox";

    private static String waitForPageToLoad = "30000";

    public SeleniumConfig(String seleniumServerHostName,
                          int seleniumServerPort,
                          String applicationServerHostName,
                          int applicationServerPort,
                          String targetBrowser){
        this.seleniumServerHostName = seleniumServerHostName;
        this.seleniumServerPort = seleniumServerPort;
        this.applicationServerHostName = applicationServerHostName;
        this.applicationServerPort = applicationServerPort;
        this.targetBrowser = targetBrowser;
    }

    public static String getSeleniumServerHostName() {
        return seleniumServerHostName;
    }

    public static int getSeleniumServerPort() {
        return seleniumServerPort;
    }

    public static String getApplicationServerHostName() {
        return applicationServerHostName;
    }

    public static int getApplicationServerPort() {
        return applicationServerPort;
    }

    public static String getTargetBrowser() {
        return targetBrowser;
    }

    public static String getWaitForPageToLoad() {
        return waitForPageToLoad;
    }

    public static void setWaitForPageToLoad(String waitForPageToLoad) {
        SeleniumConfig.waitForPageToLoad = waitForPageToLoad;
    }
}
