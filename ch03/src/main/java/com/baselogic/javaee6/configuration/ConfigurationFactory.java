package com.baselogic.javaee6.configuration;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.enterprise.inject.Produces;
import javax.enterprise.inject.spi.InjectionPoint;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.URL;
import java.util.Properties;

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
public class ConfigurationFactory {

    private static final Logger logger = LoggerFactory.getLogger(ConfigurationFactory.class);

    private volatile static Properties configProperties;
    public static final String propertiesFilePath = "C:\\usr\\SYNCH\\PACKT\\3166\\Chapters_Code\\ch03\\src\\main\\resources\\application.properties";

    public synchronized static Properties getProperties() {

        // If properties have already been loaded, do not re-load them.
        if (configProperties == null) {
            configProperties = new Properties();
            try {
                configProperties.load(new FileInputStream(propertiesFilePath));
            } catch (IOException ex) {
                logger.error(ex.getMessage(), ex);
                throw new RuntimeException(ex);
            }
        }

        return configProperties;
    }

    public @Produces @Config String getConfiguration(InjectionPoint p) {
        String configKey = p.getMember().getDeclaringClass().getName() + "." + p.getMember().getName();
        Properties config = getProperties();
        if (config.getProperty(configKey) == null) {
            configKey = p.getMember().getDeclaringClass().getSimpleName() + "." + p.getMember().getName();
            if (config.getProperty(configKey) == null)
                configKey = p.getMember().getName();
        }
        logger.error("Config key= {} value = {}", configKey, config.getProperty(configKey));

        return config.getProperty(configKey);
    }

    public @Produces @Config Double getConfigurationDouble(InjectionPoint p) {
        String val = getConfiguration(p);
        return Double.parseDouble(val);
    }
}