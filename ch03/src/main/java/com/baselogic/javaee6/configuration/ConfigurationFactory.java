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
 *
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