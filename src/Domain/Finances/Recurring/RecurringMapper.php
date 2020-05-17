<?php

namespace App\Domain\Finances\Recurring;

class RecurringMapper extends \App\Domain\Mapper {

    protected $table = 'finances_recurring';
    protected $dataobject = \App\Domain\Finances\Recurring\FinancesEntryRecurring::class;

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

    public function setLastRun($id, $timestamp) {
        $sql = "UPDATE " . $this->getTableName() . " SET last_run = :timestamp WHERE id = :id";
        $bindings = array("timestamp" => $timestamp, "id" => $id);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getSumOfAllCategories($type = 0) {
        $sql = "SELECT category, SUM(value) as sum FROM " . $this->getTableName() . " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) ";

        $bindings = array("type" => $type);
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " GROUP BY category";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSum($type = 0) {
        $sql = "SELECT SUM(value) as sum FROM " . $this->getTableName() . " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) ";

        $bindings = array("type" => $type);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

    public function getSumOfCategories($categories = array(), $type = 0) {

        $bindings = array("type" => $type, "user" => $this->user_id);
        $cat_bindings = array();
        foreach ($categories as $idx => $category) {
            $cat_bindings[":category_" . $idx] = $category;
        }



        /*
         * Since there is now support for different intervals of recurring finances we need to estimate the value for one month
         */
        /* $sql = "SELECT SUM(value) as sum FROM " . $this->getTableName() . " "
          . "WHERE type = :type "
          . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
          . "AND category IN (" . implode(", ", $keys_array) . ") ";

          $this->addSelectFilterForUser($sql, $bindings);
         */

        $where = " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
                . "AND category IN (" . implode(", ", array_keys($cat_bindings)) . ") "
                . "AND user = :user ";


        $sql = "SELECT SUM(sum) FROM ( ";
        $sql .= "   SELECT value/multiplier as sum FROM " . $this->getTableName() . " " . $where . " AND unit = 'month' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value*(4/multiplier) as sum FROM " . $this->getTableName() . " " . $where . " AND unit = 'week' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value*(30/multiplier) as sum FROM " . $this->getTableName() . " " . $where . " AND unit = 'day' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value/(12*multiplier) as sum FROM " . $this->getTableName() . " " . $where . " AND unit = 'year' ";
        $sql .= ") f";


        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($bindings, $cat_bindings));

        return intval($stmt->fetchColumn());
    }

}
