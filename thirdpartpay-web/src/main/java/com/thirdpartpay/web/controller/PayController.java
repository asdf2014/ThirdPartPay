package com.thirdpartpay.web.controller;

import com.thirdpartpay.common.model.Customer;
import com.thirdpartpay.common.service.pay.IPayService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

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
                                    Integer aimId, Long money) {
        Customer origin = new Customer();
        origin.setCustomerId(originId);

        Customer aim = new Customer();
        aim.setCustomerId(aimId);
        return payService.transfer(origin, aim, money);
    }

    /**
     * 充值
     **/
    @RequestMapping(value = "/recharge", method = RequestMethod.GET, produces = "test/plain")
    @ResponseBody
    public Boolean locationAnalyser(Integer aimID, Long money) {

        Customer aim = new Customer();
        aim.setCustomerId(aimID);
        return payService.recharge(aim, money);

    }

}
