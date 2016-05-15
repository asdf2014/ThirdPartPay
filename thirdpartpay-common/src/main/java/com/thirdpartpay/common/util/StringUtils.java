package com.thirdpartpay.common.util;

public class StringUtils {

    /**
     * 判断是否是一个空的字符串
     *
     * @param str
     * @return
     */
    public static boolean isEmpty(String str) {
        return str == null || str.length() == 0;
    }
}