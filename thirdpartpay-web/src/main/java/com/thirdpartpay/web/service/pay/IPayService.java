package com.thirdpartpay.web.service.pay;

import com.thirdpartpay.web.model.Customer;

import java.math.BigInteger;

/**
 * Created by Benedict Jin on 2016/5/9.
 */
public interface IPayService {

    //B2B P2B B2P
    boolean transfer(Customer origin, Customer aim, BigInteger money);
}
