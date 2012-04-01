package com.baselogic.chapter05.configuration;

import groovy.util.ConfigObject;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.junit.Assert.assertThat;

public class ConfigurationFactoryTest {

    private static String file = "ch05/src/test/resources/ExternalConfiguration.groovy";

    @Test
    public void testInitializeDefaultConfiguration() {

        ConfigObject config = ConfigurationFactory.init(file);

        //in groovy:
        //assert config.example.foo == "default_foo"
        //assert config.example.bar == "default_bar"

        // In Java:
        String result = (String)((ConfigObject)config.get("example")).get("foo");
        assertThat(result, is("default_foo"));

        result = (String)((ConfigObject)config.get("example")).get("bar");
        assertThat(result, is("default_bar"));
    }

    @Test
    public void testInitializeDevelopmentConfiguration() {

        ConfigObject config = ConfigurationFactory.init(file, "development");

        //in groovy:
        //assert config.example.foo == "development_foo"
        //assert config.example.bar == "development_bar"

        // In Java:
        String result = (String)((ConfigObject)config.get("example")).get("foo");
        assertThat(result, is("development_foo"));

        result = (String)((ConfigObject)config.get("example")).get("bar");
        assertThat(result, is("development_bar"));

        assertThat((Long)((ConfigObject)config.get("example")).get("baz"), is(5678L));

        assertThat((Boolean)((ConfigObject)config.get("example")).get("reloadable"), is(true));
    }

}