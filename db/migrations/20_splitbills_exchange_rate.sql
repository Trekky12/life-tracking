UPDATE splitbill_groups SET exchange_rate = 1/exchange_rate;

UPDATE splitbill_bill SET exchange_rate = 1/exchange_rate;

UPDATE splitbill_bill_recurring SET exchange_rate = 1/exchange_rate;