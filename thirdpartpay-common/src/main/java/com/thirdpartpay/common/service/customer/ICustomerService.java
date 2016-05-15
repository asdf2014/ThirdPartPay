package com.thirdpartpay.common.service.customer;


import com.thirdpartpay.common.model.Customer;
import com.thirdpartpay.common.model.CustomerExample;

import java.util.List;

/**
 * Created by Benedict Jin on 2016/4/3.
 */
public interface ICustomerService {

    int insert(Customer record);

    int deleteByExample(CustomerExample example);

    List<Customer> selectByExample(CustomerExample example);

    int updateByExampleSelective(Customer record, CustomerExample example);
}
