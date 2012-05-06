package com.baselogic.javaee6.service;

import com.baselogic.javaee6.annotations.Debugable;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.annotation.PostConstruct;
import javax.annotation.PreDestroy;
import javax.interceptor.AroundInvoke;
import javax.interceptor.AroundTimeout;
import javax.interceptor.Interceptor;
import javax.interceptor.InvocationContext;
import java.io.PrintStream;
import java.lang.annotation.Annotation;
import java.util.Arrays;

/**
 * DebugInterceptor
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
@Interceptor @Debugable
public class DebugInterceptor {

    private static final Logger logger = LoggerFactory.getLogger(UserServiceInterceptor.class);

    @AroundInvoke
    public Object aroundInvokeAdvice(InvocationContext invocationContext) throws Exception {

        logger.info("_____ start __________________________");
        logger.info("_____ DebugInterceptor _____");

        logger.info("target " + invocationContext.getTarget().getClass());

        logger.info("method {}: signature {} with annotations {} \n",
                new Object[]{invocationContext.getMethod().getName(),
                    invocationContext.getMethod(), Arrays.toString(invocationContext.getMethod().getAnnotations())}
        );

        Annotation[][] parameterAnnotations = invocationContext.getMethod().getParameterAnnotations();
        Object[] parameterValues = invocationContext.getParameters();
        Class<?>[] parameterTypes = invocationContext.getMethod().getParameterTypes();

        for (int index = 0; index < parameterValues.length; index++) {
            logger.info("param {} value={} type={} annotations={} \n",
                    new Object[]{ index,
                            parameterValues[index],
                            parameterTypes[index],
                            Arrays.toString(parameterAnnotations[index])
                    }
            );
        }

        logger.info("_____ end ___________________________");

        return invocationContext.proceed();

    }

    @AroundTimeout
    public void aroundTimeoutAdvice(InvocationContext invocationContext)
            throws Exception {
        logger.info("_____ aroundTimeoutAdvice _____");
    }

    @PostConstruct
    public void postConstruct(InvocationContext invocationContext)
            throws Exception {
        logger.info("_____ postConstruct _____");
    }


    @PreDestroy
    public void preDestroy(InvocationContext invocationContext)
            throws Exception {
        logger.info("_____ preDestroy _____");
    }

}