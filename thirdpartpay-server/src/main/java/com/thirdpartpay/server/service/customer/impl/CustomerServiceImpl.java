package com.thirdpartpay.server.service.customer.impl;

import com.thirdpartpay.common.model.Customer;
import com.thirdpartpay.common.model.CustomerExample;
import com.thirdpartpay.common.service.customer.ICustomerService;
import com.thirdpartpay.server.mapper.CustomerMapper;
import org.apache.ibatis.annotations.Param;
import org.springframework.stereotype.Service;

import javax.annotation.Resource;
import java.util.List;

/**
 * Created by Benedict Jin on 2016/4/3.
 */
@Service("customerService")
public class CustomerServiceImpl implements ICustomerService {

    @Resource
    private CustomerMapper customerMapper;

    @Override
    public int insert(Customer record) {
        return this.customerMapper.insert(record);
    }

    @Override
    public int deleteByExample(CustomerExample example) {
        return this.customerMapper.deleteByExample(example);
    }

    @Override
    public List<Customer> selectByExample(CustomerExample example) {
        return this.customerMapper.selectByExample(example);
    }

    @Override
    public int updateByExampleSelective(@Param("record") Customer record, @Param("example") CustomerExample example) {
        return this.customerMapper.updateByExampleSelective(record, example);
    }
}
