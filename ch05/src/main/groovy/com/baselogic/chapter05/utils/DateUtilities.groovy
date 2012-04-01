package com.baselogic.chapter05.utils

import java.text.SimpleDateFormat
import org.slf4j.LoggerFactory
import org.slf4j.Logger

class DateUtilities {

    public static final long MILLISECS_PER_MINUTE = 60 * 1000;
    public static final long MILLISECS_PER_HOUR = 60 * MILLISECS_PER_MINUTE;
    public static final long MILLISECS_PER_DAY = 24 * MILLISECS_PER_HOUR;

    public static String getYesterdayDate() {
        Calendar calendar = Calendar.getInstance()
        calendar.add(Calendar.DATE, -1)
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd")

        return format.format(calendar.getTime())
    }

    /**
     * returns a long equivalent to the date object passed, relative to the Unix epoch.
     *
     * @param date the date to be converted into a long.
     * @return long
     */
    public static long getUnixDayFromDate(java.util.Date date) {
        Calendar calendar = Calendar.getInstance();
        long offset = calendar.get(Calendar.ZONE_OFFSET) + calendar.get(Calendar.DST_OFFSET);
        long day = (long) Math.floor((double) (date.getTime() + offset) / ((double) MILLISECS_PER_DAY));
        return day;
    }

    /**
     * returns a long equivalent to the difference in days between the two dates passed
     * as arguments by subtracting date2 from date1.
     *
     * @param date1 the first date.
     * @param date2 the second date.
     * @return long
     */
    public static long getDateDifferenceInDays(java.util.Date date1, java.util.Date date2) {
        return (getUnixDayFromDate(date2) - getUnixDayFromDate(date1));
    }
}