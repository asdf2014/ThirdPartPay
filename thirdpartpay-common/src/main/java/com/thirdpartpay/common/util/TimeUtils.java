package com.thirdpartpay.common.util;

import java.util.Calendar;
import java.util.Date;

/**
 * Copyright @ 2015 yuzhouwan.com
 * All right reserved.
 * Function: Time Util
 *
 * @author Benedict Jin
 * @since 2016/3/8 0030
 */
public class TimeUtils {

    /**
     * 今天是几月份
     *
     * @return
     */
    public static int month() {
        Calendar calendar = Calendar.getInstance();
        return calendar.get(Calendar.MONTH) + 1;
    }

    /**
     * 某天的最后一秒 23:59:59 999
     *
     * @param index -1：今天的最后一秒；0：昨天的最后一秒；1：前天最后一秒
     * @return
     */
    public static Date yesterdayEnd(int index) {
        Calendar calendar = Calendar.getInstance();
        //设置 年月日中的“天” 为 (today - index)
        calendar.set(Calendar.DAY_OF_MONTH, calendar.get(Calendar.DAY_OF_MONTH) - index);
        calendar.set(Calendar.HOUR_OF_DAY, 0);
        calendar.set(Calendar.MINUTE, 0);
        calendar.set(Calendar.SECOND, 0);
        calendar.set(Calendar.MILLISECOND, 0);
        //减一毫秒，变成昨天的最后一秒
        return new Date(calendar.getTime().getTime() - 1);
    }

    /**
     * 某天的开始 00:00:00 000
     *
     * @param index
     * @return
     */
    public static Date pastWeekStart(int index) {
        Calendar calendar = Calendar.getInstance();
        calendar.set(Calendar.DAY_OF_MONTH, calendar.get(Calendar.DAY_OF_MONTH) - index);
        calendar.set(Calendar.HOUR_OF_DAY, 0);
        calendar.set(Calendar.MINUTE, 0);
        calendar.set(Calendar.SECOND, 0);
        calendar.set(Calendar.MILLISECOND, 0);
        return new Date(calendar.getTime().getTime());
    }
}
