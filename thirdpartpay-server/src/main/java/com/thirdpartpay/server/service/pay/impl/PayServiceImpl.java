package com.thirdpartpay.server.service.pay.impl;

import com.thirdpartpay.common.model.*;
import com.thirdpartpay.common.service.pay.IPayService;
import com.thirdpartpay.server.mapper.AccountMapper;
import com.thirdpartpay.server.mapper.CustomerAccountMapper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.math.BigInteger;
import java.util.Date;
import java.util.List;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
@Service("payService")
public class PayServiceImpl implements IPayService {

    @Autowired
    private CustomerAccountMapper customerAccountMapper;

    @Autowired
    private AccountMapper accountMapper;

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
        if(aim.getCustomerId() == null){
            return false;
        }
        Integer aimID=aim.getCustomerId();
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
