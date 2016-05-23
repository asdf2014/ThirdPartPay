package com.thirdpartpay.web.controller;

import com.thirdpartpay.common.service.customer.ICustomerDisplayService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpServletRequest;
import java.util.List;
import java.util.Map;

@Controller
@RequestMapping("/customer")
public class CustomerController {

    @Autowired
    private ICustomerDisplayService customerDisplayService;

    /**
     * 用户地域分布图
     *
     * @param request
     * @param model
     * @return
     */
    @RequestMapping(value = "/location", method = RequestMethod.GET, produces = "text/plain")
    @ResponseBody
    public List<Map<String, Object>> locationAnalyzer(HttpServletRequest request, Model model) {
        return customerDisplayService.locationAnalyzer();
    }
}