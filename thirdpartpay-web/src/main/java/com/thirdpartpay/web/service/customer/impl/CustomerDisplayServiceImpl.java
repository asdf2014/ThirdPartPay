package com.thirdpartpay.web.service.customer.impl;

import com.thirdpartpay.web.model.customer.Customer;
import com.thirdpartpay.web.model.customer.CustomerExample;
import com.thirdpartpay.web.model.customer.MultiDao;
import com.thirdpartpay.web.service.customer.ICustomerDisplayService;
import com.thirdpartpay.web.service.customer.ICustomerService;
import org.apache.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.text.DecimalFormat;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

/**
 * Created by Benedict Jin on 2016/4/18.
 */
@Service("customerDisplayService")
public class CustomerDisplayServiceImpl implements ICustomerDisplayService {

    private static Logger _log = Logger.getLogger(CustomerDisplayServiceImpl.class);
    private static final DecimalFormat df = new DecimalFormat("#.00");

    @Autowired
    private ICustomerService customerService;

    /**
     * 用户地域分布图
     *
     * @return
     */
    @Override
    public List<Map<String, Object>> locationAnalyzer() {

        CustomerExample customerExample = new CustomerExample();
        customerExample.or().andCityIsNotNull().andCountryIsNotNull();

        List<Customer> customers = customerService.selectByExample(customerExample);
        /**
         * County: <City: sum>
         */
        int total = 0;
        Map<String, Map<String, Integer>> countryAll = new HashMap<>();
        Map<String, Integer> cityAll;
        for (Customer customer : customers) {
            String country = customer.getCountry();
            String city = customer.getCity();
            if (countryAll.containsKey(country)) {
                cityAll = countryAll.get(country);
                if (cityAll.containsKey(city)) {
                    cityAll.put(city, cityAll.get(city) + 1);
                } else {
                    cityAll.put(city, 1);
                }
            } else {
                cityAll = new HashMap<>();
                cityAll.put(city, 1);
            }
            countryAll.put(country, cityAll);
            total++;
        }
        MultiDao multiDao;
        List<MultiDao> multiDaoList = new LinkedList<>();
        List<String> cityNames;
        List<Double> cityDatas;
        for (String country : countryAll.keySet()) {
            cityAll = countryAll.get(country);
            int countryCounter = 0;
            cityNames = new LinkedList<>();
            cityDatas = new LinkedList<>();
            for (String city : cityAll.keySet()) {
                int citySum = cityAll.get(city);
                cityNames.add(city);
                cityDatas.add(getPrettyFormat(total, citySum));
                countryCounter += citySum;
            }
            /**
             * {
             * y: 56.33,
             * drilldown: {
             * name: 'MSIE versions',
             * categories: ['MSIE 6.0', 'MSIE 7.0', 'MSIE 8.0', 'MSIE 9.0', 'MSIE 10.0', 'MSIE 11.0'],
             * data: [1.06, 0.5, 17.2, 8.11, 5.33, 24.13],
             * }
             * }
             */
            multiDao = new MultiDao();
            multiDao.setY(getPrettyFormat(total, countryCounter));
            multiDao.setName(country);
            multiDao.setCategories(cityNames);
            multiDao.setData(cityDatas);
            multiDaoList.add(multiDao);
        }
        return MultiDao.getMaps(multiDaoList);
    }

    private double getPrettyFormat(double total, double sumNest) {
        return 100 * Double.valueOf(df.format(sumNest / total));
    }
}
