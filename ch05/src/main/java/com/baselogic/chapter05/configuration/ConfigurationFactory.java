package com.baselogic.chapter05.configuration;

import groovy.util.ConfigObject;
import groovy.util.ConfigSlurper;

import java.io.File;
import java.io.IOException;
import java.util.Properties;

/**
 * ConfigurationFactory
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