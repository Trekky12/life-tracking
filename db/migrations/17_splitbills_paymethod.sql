ALTER TABLE splitbill_bill_recurring_users CHANGE paymethod_spend paymethod INT(11) UNSIGNED NULL DEFAULT NULL AFTER spend_foreign;

ALTER TABLE splitbill_bill_recurring_users ADD account_to int(11) UNSIGNED DEFAULT NULL REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE AFTER paymethod;
UPDATE splitbill_bill_recurring_users su JOIN finances_paymethods fp ON fp.id = su.paymethod_paid JOIN finances_accounts fa ON fa.id = fp.account SET su.account_to = fa.id;

ALTER TABLE splitbill_bill_recurring_users DROP FOREIGN KEY splitbill_bill_recurring_users_ibfk_4;
ALTER TABLE splitbill_bill_recurring_users DROP paymethod_paid;


ALTER TABLE splitbill_bill_users CHANGE paymethod_spend paymethod INT(11) UNSIGNED NULL DEFAULT NULL AFTER spend_foreign;

ALTER TABLE splitbill_bill_users ADD account_to int(11) UNSIGNED DEFAULT NULL REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE AFTER paymethod;
UPDATE splitbill_bill_users su JOIN finances_paymethods fp ON fp.id = su.paymethod_paid JOIN finances_accounts fa ON fa.id = fp.account SET su.account_to = fa.id;

ALTER TABLE splitbill_bill_users DROP FOREIGN KEY splitbill_bill_users_ibfk_4;
ALTER TABLE splitbill_bill_users DROP paymethod_paid;