package com.thirdpartpay.common.model.editable;

import java.io.Serializable;

/**
 * Created by Benedict Jin on 2016/5/17.
 */
public class EditableData implements Serializable {

    private Integer id;
    private String name;
    private String price;

//    You click like action, row: {"id":0,"name":"Item 0","price":"$0","state":false}

    public EditableData() {
    }

    public EditableData(Integer id, String name, String price) {
        this.id = id;
        this.name = name;
        this.price = price;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getPrice() {
        return price;
    }

    public void setPrice(String price) {
        this.price = price;
    }

}

