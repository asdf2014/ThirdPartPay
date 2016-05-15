package com.thirdpartpay.web.service.pay;

import com.thirdpartpay.web.model.Customer;

import java.math.BigDecimal;

/**
 * Created by Benedict Jin on 2016/5/9.
 */
public interface IPayService {

    boolean pay(Customer customer, BigDecimal money);
}
