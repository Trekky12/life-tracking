<?php

namespace App\Domain\Finances\TransactionRecurring;

class TransactionRecurringMapper extends \App\Domain\Mapper {

    protected $table = 'finances_transactions_recurring';
    protected $dataobject = \App\Domain\Finances\TransactionRecurring\TransactionRecurring::class;

    public function getAllWithNext() {
        $sql = "SELECT *, "
                . "CASE "
                . " WHEN unit = 'year' THEN DATE_ADD(last_run, INTERVAL multiplier YEAR) "
                . " WHEN unit = 'month' THEN DATE_ADD(last_run, INTERVAL multiplier MONTH) "
                . " WHEN unit = 'week' THEN DATE_ADD(last_run, INTERVAL multiplier WEEK) "
                . " WHEN unit = 'day' THEN DATE_ADD(last_run, INTERVAL multiplier DAY) "
                . " ELSE '' "
                . "END as next_run "
                . "FROM " . $this->getTableName() . " ";

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getRecurringEntries() {
        $sql = "SELECT * FROM " . $this->getTableName() . " "
                . " WHERE "
                // in date range
                . " (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
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

    public function setLastRun($id, $timestamp) {
        $sql = "UPDATE " . $this->getTableName() . " SET last_run = :timestamp WHERE id = :id";
        $bindings = array("timestamp" => $timestamp, "id" => $id);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
