package com.baselogic.javaee6.configuration;

import com.baselogic.javaee6.annotations.Config;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.enterprise.inject.Produces;
import javax.enterprise.inject.spi.Annotated;
import javax.enterprise.inject.spi.Bean;
import javax.enterprise.inject.spi.InjectionPoint;
import java.io.FileInputStream;
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
 *
 */
public class ConfigurationFactory {

    private static final Logger logger = LoggerFactory.getLogger(ConfigurationFactory.class);

    private volatile static Properties configProperties;
    //TODO FIXME Need to make this dynamic:
    public static final String propertiesFilePath = "C:\\usr\\SYNCH\\projects\\Git-Hub\\javaeecookbook\\ch03-weld-tomcat7\\src\\main\\resources\\application.properties";

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

    public @Produces @Config
    String getConfiguration(InjectionPoint injectionPoint) {
        logger.info(getInjectionPointData(injectionPoint));

        String configKey = injectionPoint.getMember().getDeclaringClass().getName() + "." + injectionPoint.getMember().getName();
        Properties config = getProperties();
        if (config.getProperty(configKey) == null) {
            configKey = injectionPoint.getMember().getDeclaringClass().getSimpleName() + "." + injectionPoint.getMember().getName();
            if (config.getProperty(configKey) == null)
                configKey = injectionPoint.getMember().getName();
        }
        logger.error("Config key= {} value = {}", configKey, config.getProperty(configKey));

        return config.getProperty(configKey);
    }

    public @Produces @Config Double getConfigurationDouble(InjectionPoint injectionPoint) {
        logger.info(getInjectionPointData(injectionPoint));

        String val = getConfiguration(injectionPoint);
        return Double.parseDouble(val);
    }


    private String getInjectionPointData(InjectionPoint injectionPoint){
        StringBuilder sb = new StringBuilder();

        sb.append("\n\n_____ InjectionPoint Data _____\n");

        sb.append("annotated " + injectionPoint.getAnnotated()).append("\n");
        sb.append("bean " + injectionPoint.getBean()).append("\n");
        sb.append("member " + injectionPoint.getMember()).append("\n");
        sb.append("qualifiers " + injectionPoint.getQualifiers()).append("\n");
        sb.append("type " + injectionPoint.getType()).append("\n");
        sb.append("isDelegate " + injectionPoint.isDelegate()).append("\n");
        sb.append("isTransient " + injectionPoint.isTransient()).append("\n");

        Bean<?> bean = injectionPoint.getBean();

        if(bean != null){
            sb.append("\n\n_____ Bean<?> Data _____\n");
            sb.append("bean.beanClass " + bean.getBeanClass()).append("\n");
            sb.append("bean.injectionPoints " + bean.getInjectionPoints()).append("\n");
            sb.append("bean.name " + bean.getName()).append("\n");
            sb.append("bean.qualifiers " + bean.getQualifiers()).append("\n");
            sb.append("bean.scope " + bean.getScope()).append("\n");
            sb.append("bean.stereotypes " + bean.getStereotypes()).append("\n");
            sb.append("bean.types " + bean.getTypes()).append("\n");
        }

        Annotated annotated = injectionPoint.getAnnotated();
        sb.append("\n\n_____ Annotated Data _____\n");
        sb.append("annotated.annotations " + annotated.getAnnotations()).append("\n");
        sb.append("annotated.annotations " + annotated.getBaseType()).append("\n");
        sb.append("annotated.typeClosure " + annotated.getTypeClosure()).append("\n");

        sb.append("\n_______________________________\n");

        return sb.toString();
    }
}