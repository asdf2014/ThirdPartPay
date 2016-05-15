package com.thirdpartpay.web.controller;

import com.thirdpartpay.common.str.StringUtils;
import com.thirdpartpay.web.model.Customer;
import com.thirdpartpay.web.model.CustomerExample;
import com.thirdpartpay.web.service.customer.ICustomerDisplayService;
import com.thirdpartpay.web.service.customer.ICustomerService;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.annotation.Resource;
import javax.servlet.http.HttpServletRequest;
import java.util.Arrays;
import java.util.List;
import java.util.Map;

@Controller
@RequestMapping("/customer")
public class CustomerController {

    @Resource
    private ICustomerService customerService;

    @Resource
    private ICustomerDisplayService customerDisplayService;

    //过时的用法
    @RequestMapping("/query")
    public String queryCustomerByID(HttpServletRequest request, Model model) {

        String customerIdStr = request.getParameter("customerId");
        if (StringUtils.isEmpty(customerIdStr)) {
            model.addAttribute("error", "customerId is null");
            return "error";
        }
        int customerId;
        try {
            customerId = Integer.parseInt(customerIdStr);
        } catch (Exception e) {
            model.addAttribute("error", "customerId[".concat(customerIdStr).concat("] is not a valid number!"));
            return "error";
        }

        CustomerExample customerExample = new CustomerExample();
        customerExample.or().andCustomerIdIn(Arrays.asList(customerId));

        List<Customer> customers = customerService.selectByExample(customerExample);
        if (customers == null || customers.size() > 0) {
            model.addAttribute("customer", customers.get(0));
        } else {
            model.addAttribute("error", "There is no customer that id is [".concat(customerId + "").concat("]."));
            return "error";
        }
        return "showCustomer";
    }

    /**
     * 对数据仓库进行 多维解析
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