package com.thirdpartpay.web.controller;

import com.thirdpartpay.common.model.editable.EditableData;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

/**
 * Created by Benedict Jin on 2016/5/17.
 */
@Controller
@RequestMapping("/editable")
public class EditableController {

    @RequestMapping(value = "/data", method = RequestMethod.GET/*, produces = "test/plain"*/)
    @ResponseBody
    public Map<String, Object> businessRecoder(String order, Integer offset, Integer limit) {

        //order=asc&offset=0&limit=10
        List<EditableData> editableDataList = new LinkedList<>();
        editableDataList.add(new EditableData(1, "cloud1", "100"));
        editableDataList.add(new EditableData(2, "cloud2", "101"));
        editableDataList.add(new EditableData(3, "cloud3", "102"));
        editableDataList.add(new EditableData(4, "cloud4", "103"));
        editableDataList.add(new EditableData(5, "cloud5", "104"));

        Map<String, Object> data = new HashMap<>();
        data.put("total", 800);
        data.put("rows", editableDataList);
        return data;
    }
}
