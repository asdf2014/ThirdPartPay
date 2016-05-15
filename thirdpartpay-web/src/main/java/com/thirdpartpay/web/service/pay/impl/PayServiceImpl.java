package com.thirdpartpay.web.service.pay.impl;

import com.thirdpartpay.web.mapper.AccountMapper;
import com.thirdpartpay.web.mapper.CustomerAccountMapper;
import com.thirdpartpay.web.model.*;
import com.thirdpartpay.web.service.pay.IPayService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.math.BigInteger;
import java.util.Date;
import java.util.List;

/**
 * Created by Benedict Jin on 2016/5/15.
 */
@Service("payServiceImpl")
public class PayServiceImpl implements IPayService {

    @Autowired
    private CustomerAccountMapper customerAccountMapper;

    @Autowired
    private AccountMapper accountMapper;

    @Override
    public boolean transfer(Customer origin, Customer aim, BigInteger money) {

        //TODO:事务
        if (origin.getCustomerId() == null || aim.getCustomerId() == null) {
            return false;
        }

        if (!reduceOrigin(origin.getCustomerId(), money)) {
            return false;
        }
        if (!addAim(aim.getCustomerId(), money)) {
            return false;
        }
        return true;
    }

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
