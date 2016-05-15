package com.thirdpartpay.web.service.pay.impl;

import com.thirdpartpay.web.model.Customer;
import com.thirdpartpay.web.service.pay.IPayService;

import java.math.BigDecimal;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
public class PayServiceImpl implements IPayService {

    @Override
    public boolean pay(Customer customer, BigDecimal money) {
        return false;
    }
}
