package com.baselogic.chapter05.aspects;

import org.aspectj.lang.ProceedingJoinPoint;
import org.aspectj.lang.annotation.Around;
import org.aspectj.lang.annotation.Aspect;

import java.util.Random;

@Aspect
public class MockDelaySimulationAspect {

    @Around("within(com.baselogic.package.to.simulate.delays..*)")
    public Object simulateRandomDelays(ProceedingJoinPoint call) throws Throwable {
        Object point = null;

        Random random = new Random();
        final int randomSleepTimeInMs = 20000; // 20 seconds

		try {
			Thread.sleep(random.nextInt(randomSleepTimeInMs) + 1);
			point = call.proceed();
		} catch (Throwable t) {
			throw t;
		} finally {
		}
		return point;
	}
}
