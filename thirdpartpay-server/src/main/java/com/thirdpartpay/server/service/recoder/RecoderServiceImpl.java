package com.thirdpartpay.server.service.recoder;

import com.thirdpartpay.common.model.BusinessRecoder;
import com.thirdpartpay.common.service.recoder.IRecoderService;
import com.thirdpartpay.server.mapper.BusinessRecoderMapper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

/**
 * 交易记录
 * <p>
 * Created by Benedict Jin on 2016/5/21.
 */
@Service("recoderService")
public class RecoderServiceImpl implements IRecoderService {

    @Autowired
    private BusinessRecoderMapper businessRecoderMapper;

    /**
     * 添加一条交易记录，到数据库中
     *
     * @param businessRecoder
     */
    @Override
    public void recoderNote(BusinessRecoder businessRecoder) {

        businessRecoderMapper.insert(businessRecoder);
    }
}
