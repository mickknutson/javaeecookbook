package com.baselogic.chapter05.configuration;

import groovy.util.ConfigObject;
import groovy.util.ConfigSlurper;

import java.io.File;
import java.io.IOException;
import java.util.Properties;

public final class ConfigurationFactory {

    private static ConfigObject config = null;

    private static boolean isReloadable = true;

    public static ConfigObject init(String configurationFile){
        return init(configurationFile, null);
    }

    public static ConfigObject init(String file, String environment){

        try{
            if(config == null || isReloadable){
                synchronized (ConfigObject.class){
                    config = new ConfigSlurper(environment).parse(new File(file).toURL());
                }
            }
        } catch(IOException e){
            throw new RuntimeException("IOException initializing Groovy ConfigObject: " + e.getMessage(), e);
        }

        return config;
    }

    public static Properties initProperties(String configurationFile){
        Properties props = null;

        init(configurationFile, null);

        if(config!= null && !config.isEmpty()){
            props = config.toProperties();
        }

        return props;
    }

    public static String parseConfigProperty(ConfigObject config, String property){

        /*String[] elements = null;//StringTokenizer(property, '.');
        ConfigObject configElement = config;
        for(String element: elements){
            if(configElement.containsKey())
            configElement = (ConfigObject)configElement.get(element);
        }*/

        return "";
    }
}