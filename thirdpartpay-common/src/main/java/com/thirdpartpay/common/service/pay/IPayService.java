package com.thirdpartpay.common.service.pay;


import com.thirdpartpay.common.model.Customer;

import java.util.List;

/**
 * Created by Benedict Jin on 2016/5/9.
 */
public interface IPayService {

    //B2B P2B B2P
    boolean transfer(Customer origin, Customer aim, Long money);

    boolean recharge(Customer aim, Long money);

    List<Long> businessRecoder();
}
