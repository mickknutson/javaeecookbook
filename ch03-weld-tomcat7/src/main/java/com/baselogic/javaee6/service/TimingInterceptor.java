package com.baselogic.javaee6.service;

import com.baselogic.javaee6.annotations.Debugable;
import com.baselogic.javaee6.annotations.Timer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.interceptor.AroundInvoke;
import javax.interceptor.Interceptor;
import javax.interceptor.InvocationContext;
import java.lang.annotation.Annotation;

/**
 * TimingInterceptor
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
@Interceptor @Timer
public class TimingInterceptor {

    private static final Logger logger = LoggerFactory.getLogger(UserServiceInterceptor.class);

    @AroundInvoke
    public Object aroundInvokeAdvice(InvocationContext ctx)
            throws Exception {

        logger.info("In TimingInterceptor");

        logger.info(">>> ----- start timer...>>>");
        long start = System.currentTimeMillis();

        Object obj = ctx.proceed();

        long end = System.currentTimeMillis();

        logger.info(">>> ----- method took {} milliseconds to complete...>>>", (end-start));

        return obj;
    }
}