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

        if (!reduceOrigin(origin.getCustomerId(), new BigInteger(money + ""))) {
            return false;
        }
        if (!addAim(aim.getCustomerId(), new BigInteger(money + ""))) {
            return false;
        }
        return true;
    }

    @Override
    public boolean recharge(Customer aim, Long money) {
        if (aim.getCustomerId() == null) {
            return false;
        }
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
        Long remain = accountMapper.selectByPrimaryKey(accountId).getRemain();
        if (remain == null) {
            remain = 0L;
        }
        Account account = new Account();
        account.setAccountId(accountId);
        account.setModifyDate(new Date());
        account.setRemain(remain + money.longValue());

        AccountExample accountExample = new AccountExample();
        accountExample.or().andAccountIdEqualTo(accountId);
        accountMapper.updateByExample(account, accountExample);
        return true;

    }

    @Override
    public List<Long> businessRecoder() {

        BusinessRecoderExample businessRecoderExample = new BusinessRecoderExample();
        // 取出过去一周内的交易记录
        businessRecoderExample.or().andStartTimeBetween(TimeUtils.pastWeekStart(7), TimeUtils.yesterdayEnd(0));

        Map<Integer, Long> week = new HashMap<>();
        for (int i = 0; i < 7; i++) {
            week.put(i, 0L);
        }
        List<Long> result = new LinkedList<>();
        List<BusinessRecoder> businessRecoderList = businessRecoderMapper.selectByExample(businessRecoderExample);

        if (businessRecoderList == null || businessRecoderList.size() == 0)
            return result;

        long sum = 0L;
        for (BusinessRecoder businessRecoder : businessRecoderList) {

            Long startTime = businessRecoder.getStartTime().getTime();
            Long money = businessRecoder.getMoney();
            sum += money;
            if (startTime >= TimeUtils.pastWeekStart(7).getTime()
                    && startTime < TimeUtils.yesterdayEnd(6).getTime()) {
                if (week.containsKey(0)) {
                    week.put(0, week.get(0) + money);
                } else {
                    week.put(0, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(6).getTime()
                    && startTime < TimeUtils.yesterdayEnd(5).getTime()) {
                if (week.containsKey(1)) {
                    week.put(1, week.get(1) + money);
                } else {
                    week.put(1, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(5).getTime()
                    && startTime < TimeUtils.yesterdayEnd(4).getTime()) {
                if (week.containsKey(2)) {
                    week.put(2, week.get(2) + money);
                } else {
                    week.put(2, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(4).getTime()
                    && startTime < TimeUtils.yesterdayEnd(3).getTime()) {
                if (week.containsKey(3)) {
                    week.put(3, week.get(3) + money);
                } else {
                    week.put(3, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(3).getTime()
                    && startTime < TimeUtils.yesterdayEnd(2).getTime()) {
                if (week.containsKey(4)) {
                    week.put(4, week.get(4) + money);
                } else {
                    week.put(4, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(2).getTime()
                    && startTime < TimeUtils.yesterdayEnd(1).getTime()) {
                if (week.containsKey(5)) {
                    week.put(5, week.get(5) + money);
                } else {
                    week.put(5, money);
                }
            } else if (startTime >= TimeUtils.pastWeekStart(1).getTime()
                    && startTime < TimeUtils.yesterdayEnd(0).getTime()) {
                if (week.containsKey(6)) {
                    week.put(6, week.get(6) + money);
                } else {
                    week.put(6, money);
                }
            }
        }
        for (Integer day : week.keySet()) {
//            result.add(day, (long) (week.get(day) / (double) sum) * 100);
            result.add(day, week.get(day));
        }
        return result;
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
            remain = 0l;
        }
        Account account = new Account();
        account.setAccountId(accountId);
        account.setModifyDate(new Date());
        account.setRemain(remain + money.longValue());

        AccountExample accountExample = new AccountExample();
        accountExample.or().andAccountIdEqualTo(accountId);
        accountMapper.updateByExample(account, accountExample);
        return true;
    }

}
