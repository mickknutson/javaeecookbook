package com.baselogic.chapter05.utils;

import org.junit.Test;

import static org.junit.Assert.assertEquals;

import java.text.SimpleDateFormat;
import java.util.Calendar;

public class DateUtilitiesJavaTest {

    @Test
    public void testGetYesterdayDate() {
        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.DATE, -1);
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        String expected = format.format(calendar.getTime());

        String result = DateUtilities.getYesterdayDate();
        // Aspect DontWriteToTheConsole will complain:
        // System.out.println(result);
        assertEquals(expected, result);
    }

    @Test
    public void testGetYesterdayDateScala() {
        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.DATE, -1);
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        String expected = format.format(calendar.getTime());

        String result = ScalaDateUtilities.getYesterdayDate();
        assertEquals(expected, result);
    }
}