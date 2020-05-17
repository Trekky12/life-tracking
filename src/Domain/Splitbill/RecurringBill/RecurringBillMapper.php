<?php

namespace App\Domain\Splitbill\RecurringBill;

use App\Domain\Splitbill\BaseBillMapper;

class RecurringBillMapper extends BaseBillMapper {

    protected $table = "splitbill_bill_recurring";
    protected $dataobject = \App\Domain\Splitbill\RecurringBill\RecurringBill::class;
    protected $bill_balance_table = "splitbill_bill_recurring_users";

    public function getRecurringBills($group) {

        $bindings = array("user" => $this->user_id, "group" => $group);
        $sql = "SELECT b.*, bb.spend, bb.paid, bb.paid-bb.spend as balance, "
                . "CASE "
                . " WHEN unit = 'year' THEN DATE_ADD(last_run, INTERVAL multiplier YEAR) "
                . " WHEN unit = 'month' THEN DATE_ADD(last_run, INTERVAL multiplier MONTH) "
                . " WHEN unit = 'week' THEN DATE_ADD(last_run, INTERVAL multiplier WEEK) "
                . " WHEN unit = 'day' THEN DATE_ADD(last_run, INTERVAL multiplier DAY) "
                . " ELSE '' "
                . "END as next_run "
                . " FROM " . $this->getTableName() . " b "
                . " LEFT JOIN " . $this->getTableName($this->bill_balance_table) . " bb "
                . " ON b.id = bb.bill AND bb.user = :user "
                . " WHERE b.sbgroup = :group "
                . " ORDER BY createdOn DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function setLastRun($id, $timestamp) {
        $sql = "UPDATE " . $this->getTableName() . " SET last_run = :timestamp WHERE id = :id";
        $bindings = array("timestamp" => $timestamp, "id" => $id);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getRecurringEntries() {
        $sql = "SELECT * FROM " . $this->getTableName() . " "
                . " WHERE "
                // in date range
                . " (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
                // start day is today
                //. " AND ( DAY(start) >= DAY(CURRENT_DATE()) OR start IS NULL)"
                // interval
                . " AND "
                . "("
                . "  ("
                // yearly
                . "     ( DATEDIFF(NOW(), DATE_ADD(last_run, INTERVAL multiplier YEAR)) >= 0 AND unit = 'year' ) OR"
                // monthly
                . "     ( DATEDIFF(NOW(), DATE_ADD(last_run, INTERVAL multiplier MONTH)) >= 0 AND unit = 'month' ) OR "
                // weekly
                . "     ( DATEDIFF(NOW(), DATE_ADD(last_run, INTERVAL multiplier WEEK)) >= 0 AND unit = 'week' ) OR "
                // daily
                . "     ( DATEDIFF(NOW(), DATE_ADD(last_run, INTERVAL multiplier DAY)) >= 0 AND unit = 'day' ) "
                // not runned
                . "  ) OR last_run IS NULL"
                . ")"
                . " AND "
                . "is_active > 0";


        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function updateLastRun(array $ids) {

        $sql = "UPDATE " . $this->getTableName() . " SET last_run = CURRENT_TIMESTAMP WHERE id in (" . implode(',', $ids) . ")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
