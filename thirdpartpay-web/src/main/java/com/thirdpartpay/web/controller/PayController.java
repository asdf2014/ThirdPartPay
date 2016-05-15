package com.thirdpartpay.web.controller;

import com.thirdpartpay.web.model.Customer;
import com.thirdpartpay.web.service.pay.IPayService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import java.math.BigInteger;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
@Controller
@RequestMapping("/pay")
public class PayController {

    @Autowired
    private IPayService payService;

    /**
     * 转账
     *
     * @return
     */
    @RequestMapping(value = "/transfer", method = RequestMethod.GET, produces = "text/plain")
    @ResponseBody
    public Boolean locationAnalyzer(Integer originId,
                                    Integer aimId, BigInteger money) {
        Customer origin = new Customer();
        origin.setCustomerId(originId);

        Customer aim = new Customer();
        aim.setCustomerId(aimId);
        return payService.transfer(origin, aim, money);
    }

}
