package com.thirdpartpay.server.service.recoder;

import com.thirdpartpay.common.model.BusinessRecoder;
import com.thirdpartpay.common.service.recoder.IRecoderService;
import com.thirdpartpay.server.mapper.BusinessRecoderMapper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

/**
 * Created by Benedict Jin on 2016/5/21.
 */
@Service("recoderService")
public class RecoderServiceImpl implements IRecoderService {

    @Autowired
    private BusinessRecoderMapper businessRecoderMapper;

    @Override
    public void recoderNote(BusinessRecoder businessRecoder) {

        businessRecoderMapper.insert(businessRecoder);
    }
}
