package com.baselogic.test;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * [Class_Name]
 *
 * @author Mick Knutson
 *         <a href="http://www.baselogic.com>Blog</a>< /br>
 *         <a href="http://linkedin.com/in/mickknutson>LinkedIN</a>< /br>
 *         <a href="http://twitter.com/mickknutson>Twitter</a>< /br>
 *         <a href="http://www.mickknutson.com>Personal</a>< /br>
 * @since 2011
 * <i>To change this template use File | Settings | File Templates.</i>
 */
public aspect TestUtilityAspects {

    private static final Logger logger = LoggerFactory.getLogger(TestUtilityAspects.class);

    pointcut methodTiming() : execution(public * *.testNONE*(..));
    Object around() : methodTiming() {
        long start = System.nanoTime();
        Object ret = proceed();
        long end = System.nanoTime();
        logger.info("--------------------");
        logger.info("{} took {} nanoseconds.", thisJoinPointStaticPart.getSignature(), (end - start));
        logger.info("--------------------");
        return ret;
    }
}