package com.thirdpartpay.common.model.customer;

import java.io.Serializable;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

/**
 * 用户地域分布图 实体类，存放必需的几个参数（每个 MultiDao代表了一个 country内每个 city的百分比）
 * <p>
 * Created by Benedict Jin on 2016/4/16.
 */
public class MultiDao implements Serializable {

    /**
     * {
     *      y: 56.33,
     *      drilldown: {
     *              name: 'MSIE versions',
     *              categories: ['MSIE 6.0', 'MSIE 7.0', 'MSIE 8.0', 'MSIE 9.0', 'MSIE 10.0', 'MSIE 11.0'],
     *              data: [1.06, 0.5, 17.2, 8.11, 5.33, 24.13],
     *      }
     * }
     */
    //这个国家的人口，占全球百分比
    private Double y;
    //国家的名称
    private String name;
    //这个国家的各个城市的名称
    private List<String> categories;
    //各个城市，对应占全球百分比
    private List<Double> data;

    public MultiDao() {
    }

    public MultiDao(Double y, String name, List<String> categories, List<Double> data) {
        this.y = y;
        this.name = name;
        this.categories = categories;
        this.data = data;
    }

    /**
     * 封装为前端绘 用户地域分布图 所需的 数据格式
     *
     * @param multiDaoList
     * @return
     */
    public static List<Map<String, Object>> getMaps(List<MultiDao> multiDaoList) {

        List<Map<String, Object>> result = new LinkedList<>();
        MultiDao multiDao;
        for (int i = 0; i < multiDaoList.size(); i++) {
            multiDao = multiDaoList.get(i);

            Map<String, Object> params = new HashMap<>();
            params.put("y", multiDao.y);

            Map<String, Object> drilldown = new HashMap<>();
            drilldown.put("name", multiDao.name);
            drilldown.put("categories", multiDao.categories);
            drilldown.put("data", multiDao.data);
            params.put("drilldown", drilldown);

            result.add(params);
        }
        return result;
    }

    public Double getY() {
        return y;
    }

    public void setY(Double y) {
        this.y = y;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public List<String> getCategories() {
        return categories;
    }

    public void setCategories(List<String> categories) {
        this.categories = categories;
    }

    public List<Double> getData() {
        return data;
    }

    public void setData(List<Double> data) {
        this.data = data;
    }
}