package com.thirdpartpay.web.example;

import com.thirdpartpay.web.model.Customer;
import com.thirdpartpay.web.model.CustomerExample;
import com.thirdpartpay.web.service.customer.ICustomerService;
import org.apache.log4j.Logger;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.test.context.ContextConfiguration;
import org.springframework.test.context.junit4.SpringJUnit4ClassRunner;
import org.springframework.test.context.web.WebAppConfiguration;

import java.util.Arrays;
import java.util.List;

/**
 * Created by Benedict Jin on 2016/4/3.
 */
@RunWith(SpringJUnit4ClassRunner.class)
@WebAppConfiguration(value = "")
@ContextConfiguration(locations = {"classpath:/spring/spring-mybatis.xml"})
public class MyBatisSpringTest {

    private static Logger _log = Logger.getLogger(MyBatisSpringTest.class);

    @Autowired
    private ICustomerService customerService;

    @Test
    public void testInsert() {
        Customer customer = new Customer();
        customer.setCustomerId(2);
        customer.setCustomerName("Jin");
        customer.setCustomerPassword("123456");
        customer.setEmail("grace@gmail.com");
        _log.debug(customer.toString());

        customerService.insert(customer);
    }


    @Test
    public void testDelete() {

        CustomerExample customerExample = new CustomerExample();
        customerExample.or().andCustomerIdIn(Arrays.asList(2));

        customerService.deleteByExample(customerExample);
    }

    @Test
    public void testQuery() {
        CustomerExample customerExample = new CustomerExample();
        customerExample.or().andCustomerIdIn(Arrays.asList(1));

        List<Customer> customers = customerService.selectByExample(customerExample);
        for (Customer customer : customers) {
            _log.info(customer.getCustomerName());
        }
    }

    @Test
    public void testUpdate() {

        Customer customer = new Customer();
        customer.setCustomerPassword("654321");
        _log.debug(customer.toString());

        CustomerExample customerExample = new CustomerExample();
        customerExample.or().andCustomerIdIn(Arrays.asList(1));

        customerService.updateByExampleSelective(customer, customerExample);
    }

}
