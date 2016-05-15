package com.thirdpartpay.web.service.customer;

import com.thirdpartpay.web.model.Customer;
import com.thirdpartpay.web.model.CustomerExample;
import org.apache.ibatis.annotations.Param;

import java.util.List;

/**
 * Created by Benedict Jin on 2016/4/3.
 */
public interface ICustomerService {

    int insert(Customer record);

    int deleteByExample(CustomerExample example);

    List<Customer> selectByExample(CustomerExample example);

    int updateByExampleSelective(@Param("record") Customer record, @Param("example") CustomerExample example);
}
