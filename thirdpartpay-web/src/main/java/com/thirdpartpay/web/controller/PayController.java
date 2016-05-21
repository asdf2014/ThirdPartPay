package com.thirdpartpay.web.controller;

import com.thirdpartpay.common.model.BusinessRecoder;
import com.thirdpartpay.common.model.Customer;
import com.thirdpartpay.common.service.pay.IPayService;
import com.thirdpartpay.common.service.recoder.IRecoderService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.Date;
import java.util.List;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
@Controller
@RequestMapping("/pay")
public class PayController {

    @Autowired
    private IPayService payService;

    @Autowired
    private IRecoderService recoderNote;

    /**
     * 转账
     *
     * @return
     */
    @RequestMapping(value = "/transfer", method = RequestMethod.GET, produces = "text/plain")
    @ResponseBody
    public Boolean locationAnalyzer(Integer originId,
                                    Integer aimId, Long money) {
        BusinessRecoder br = new BusinessRecoder();
        br.setStartTime(new Date());
        br.setFirstPart(originId);
        br.setSecondPart(aimId);
        br.setMoney(money);

        Customer origin = new Customer();
        origin.setCustomerId(originId);

        Customer aim = new Customer();
        aim.setCustomerId(aimId);
        boolean isSuccess = payService.transfer(origin, aim, money);
        br.setEndTime(new Date());
        br.setFlag(isSuccess ? 0 : 1);
        recoderNote.recoderNote(br);
        return isSuccess;
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

    /**
     * 展示过去一周内的交易额
     **/
    @RequestMapping(value = "/recoder", method = RequestMethod.GET, produces = "test/plain")
    @ResponseBody
    public List<Long> businessRecoder() {

        return payService.businessRecoder();
    }

}
