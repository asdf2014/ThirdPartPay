package com.thirdpartpay.server.service.pay.impl;

import com.thirdpartpay.common.model.*;
import com.thirdpartpay.common.service.pay.IPayService;
import com.thirdpartpay.common.util.TimeUtils;
import com.thirdpartpay.server.mapper.AccountMapper;
import com.thirdpartpay.server.mapper.BusinessRecoderMapper;
import com.thirdpartpay.server.mapper.CustomerAccountMapper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.math.BigInteger;
import java.util.*;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
@Service("payService")
public class PayServiceImpl implements IPayService {

    /**
     * 控制从过去第几天 开始统计，例如，
     * index=6, date=2016-5-23 ，则统计范围：2016-5-23~2016-5-27
     * index=6, date=2016-5-23 ，则统计范围：2016-5-22~2016-5-26
     */
    private static final int index = 6;

    @Autowired
    private CustomerAccountMapper customerAccountMapper;

    @Autowired
    private AccountMapper accountMapper;

    @Autowired
    private BusinessRecoderMapper businessRecoderMapper;

    @Override
    public boolean transfer(Customer origin, Customer aim, Long money) {

        //TODO:事务
        if (origin.getCustomerId() == null || aim.getCustomerId() == null) {
            return false;
        }
        //从源账号里面扣除 money
        if (!reduceOrigin(origin.getCustomerId(), new BigInteger(money + ""))) {
            return false;
        }
        //往目标账号里面增加 money
        if (!addAim(aim.getCustomerId(), new BigInteger(money + ""))) {
            return false;
        }
        return true;
    }

    /**
     * 往某个账户里 充值
     *
     * @param aim
     * @param money
     * @return
     */
    @Override
    public boolean recharge(Customer aim, Long money) {
        if (aim.getCustomerId() == null) {
            return false;
        }
        //依据 账号ID，查找客户
        Integer aimID = aim.getCustomerId();
        CustomerAccountExample customerAccountExample = new CustomerAccountExample();
        customerAccountExample.or().andCustomerIdEqualTo(aimID);
        List<CustomerAccount> customerAccounts = customerAccountMapper.selectByExample(customerAccountExample);
        //查不到客户
        if (customerAccounts == null || customerAccounts.size() == 0) {
            return false;
        }
        Integer accountId = customerAccounts.get(0).getAccountId();
        if (accountId == null) {
            return false;
        }
        //查看账户余额
        Long remain = accountMapper.selectByPrimaryKey(accountId).getRemain();
        if (remain == null) {
            remain = 0L;
        }
        Account account = new Account();
        account.setAccountId(accountId);
        account.setModifyDate(new Date());
        account.setRemain(remain + money.longValue());

        //完成最终的扣除动作
        AccountExample accountExample = new AccountExample();
        accountExample.or().andAccountIdEqualTo(accountId);
        accountMapper.updateByExample(account, accountExample);
        return true;

    }

    /**
     * 先从源账户扣除 money
     *
     * @param customerId
     * @param money
     * @return
     */
    private boolean reduceOrigin(Integer customerId, BigInteger money) {
        Integer accountId = customerAccountMapper.selectByPrimaryKey(customerId).getAccountId();
        //查不到客户
        if (accountId == null || accountId <= 0) {
            return false;
        }
        Long remain = accountMapper.selectByPrimaryKey(accountId).getRemain();
        //账户余额不足
        if (remain == null || new BigInteger(remain + "").compareTo(money) == -1) {
            return false;
        }
        //完成余额扣除
        Account account = new Account();
        account.setAccountId(accountId);
        account.setModifyDate(new Date());
        account.setRemain(remain - money.longValue());

        AccountExample accountExample = new AccountExample();
        accountExample.or().andAccountIdEqualTo(accountId);
        accountMapper.updateByExample(account, accountExample);
        return true;
    }

    /**
     * 后往目标账户增加 money
     *
     * @param customerId
     * @param money
     * @return
     */
    private boolean addAim(Integer customerId, BigInteger money) {
        //依据 账号ID，查找客户
        CustomerAccountExample customerAccountExample = new CustomerAccountExample();
        customerAccountExample.or().andCustomerIdEqualTo(customerId);
        List<CustomerAccount> customerAccounts = customerAccountMapper.selectByExample(customerAccountExample);
        //查不到客户
        if (customerAccounts == null || customerAccounts.size() == 0) {
            return false;
        }
        Integer accountId = customerAccounts.get(0).getAccountId();
        if (accountId == null || accountId <= 0) {
            return false;
        }
        Long remain = accountMapper.selectByPrimaryKey(accountId).getRemain();
        //账户余额不足
        if (remain == null) {
            remain = 0L;
        }
        Account account = new Account();
        account.setAccountId(accountId);
        account.setModifyDate(new Date());
        account.setRemain(remain + money.longValue());
        //完成最终的增加 money的动作
        AccountExample accountExample = new AccountExample();
        accountExample.or().andAccountIdEqualTo(accountId);
        accountMapper.updateByExample(account, accountExample);
        return true;
    }

    /**
     * 用来给首页展示过去一周的交易额 波形图
     *
     * @return
     */
    @Override
    public List<Long> businessRecoder() {

        BusinessRecoderExample businessRecoderExample = new BusinessRecoderExample();
        // 取出过去一周内的交易记录，即（6天前的最开始一秒，到今天的最后一秒）
        businessRecoderExample.or().andStartTimeBetween(TimeUtils.pastWeekStart(index),
                TimeUtils.yesterdayEnd(index - 7));

        Map<Integer, Long> week = new HashMap<>();
        for (int i = 0; i < 7; i++) {
            week.put(i, 0L);
        }
        List<Long> result = new LinkedList<>();
        List<BusinessRecoder> businessRecoderList = businessRecoderMapper.selectByExample(businessRecoderExample);
        //判断是否能找到，过去一周内交易记录
        if (businessRecoderList == null || businessRecoderList.size() == 0) {
            return result;
        }
        //找到交易记录
        for (BusinessRecoder businessRecoder : businessRecoderList) {
            //按照时间进行统计，将过去一周的数据，统计出每天的总交易额
            Long startTime = businessRecoder.getStartTime().getTime();
            Long money = businessRecoder.getMoney();
            //6天前的最开始一秒，到 6天前的最后一秒
            if (startTime >= TimeUtils.pastWeekStart(index).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 1).getTime()) {
                if (week.containsKey(0)) {
                    week.put(0, week.get(0) + money);
                } else {
                    week.put(0, money);
                }
                //5天前
            } else if (startTime >= TimeUtils.pastWeekStart(index - 1).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 2).getTime()) {
                if (week.containsKey(1)) {
                    week.put(1, week.get(1) + money);
                } else {
                    week.put(1, money);
                }
                //4天前
            } else if (startTime >= TimeUtils.pastWeekStart(index - 2).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 3).getTime()) {
                if (week.containsKey(2)) {
                    week.put(2, week.get(2) + money);
                } else {
                    week.put(2, money);
                }
                //3天前
            } else if (startTime >= TimeUtils.pastWeekStart(index - 3).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 4).getTime()) {
                if (week.containsKey(3)) {
                    week.put(3, week.get(3) + money);
                } else {
                    week.put(3, money);
                }
                //2天前
            } else if (startTime >= TimeUtils.pastWeekStart(index - 4).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 5).getTime()) {
                if (week.containsKey(4)) {
                    week.put(4, week.get(4) + money);
                } else {
                    week.put(4, money);
                }
                //1天前
            } else if (startTime >= TimeUtils.pastWeekStart(index - 5).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 6).getTime()) {
                if (week.containsKey(5)) {
                    week.put(5, week.get(5) + money);
                } else {
                    week.put(5, money);
                }
                //当天
            } else if (startTime >= TimeUtils.pastWeekStart(index - 6).getTime()
                    && startTime < TimeUtils.yesterdayEnd(index - 7).getTime()) {
                if (week.containsKey(6)) {
                    week.put(6, week.get(6) + money);
                } else {
                    week.put(6, money);
                }
            }
        }
        //只保留过去 7天，每天对应的 交易总额
        for (Integer day : week.keySet()) {
            result.add(day, week.get(day));
        }
        return result;
    }
}