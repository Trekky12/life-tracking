ALTER TABLE finances_transactions ADD is_exchange_fee INT(1) DEFAULT 0 AFTER is_round_up_savings;

ALTER TABLE finances
  ADD COLUMN transaction_exchange_fee INT(11) UNSIGNED DEFAULT NULL AFTER transaction_round_up_savings,
  ADD CONSTRAINT finances_ibfk_7 FOREIGN KEY (transaction_exchange_fee)
    REFERENCES finances_transactions(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;