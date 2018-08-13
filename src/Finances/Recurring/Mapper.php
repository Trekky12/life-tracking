<?php

namespace App\Finances\Recurring;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_recurring';
    protected $model = '\App\Finances\Recurring\FinancesEntryRecurring';

    public function getRecurringEntries() {
        $sql = "SELECT * FROM " . $this->getTable() . " "
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
                . ")";


        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function updateLastRun(array $ids) {

        $sql = "UPDATE " . $this->getTable() . " SET last_run = CURRENT_TIMESTAMP WHERE id in (" . implode(',', $ids) . ")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
    public function setLastRun($id, $timestamp){
        $sql = "UPDATE " . $this->getTable() . " SET last_run = :timestamp WHERE id = :id";
        $bindings = array("timestamp" => $timestamp, "id" => $id);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getSumOfAllCategories($type = 0) {
        $sql = "SELECT category, SUM(value) as sum FROM " . $this->getTable() . " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) ";

        $bindings = array("type" => $type);
        $this->filterByUser($sql, $bindings);

        $sql .= " GROUP BY category";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSum($type = 0) {
        $sql = "SELECT SUM(value) as sum FROM " . $this->getTable() . " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) ";

        $bindings = array("type" => $type);
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

    public function getSumOfCategories($categories = array(), $type = 0) {

        $bindings = array("type" => $type, "user" => $this->userid);
        $keys_array = array();
        foreach ($categories as $idx => $category) {
            $bindings["category" . $idx] = $category;
            $keys_array[] = ":category" . $idx . "";
        }


        
        /*
         * Since there is now support for different intervals of recurring finances we need to estimate the value for one month
         */
        /* $sql = "SELECT SUM(value) as sum FROM " . $this->getTable() . " "
          . "WHERE type = :type "
          . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
          . "AND category IN (" . implode(", ", $keys_array) . ") ";

          $this->filterByUser($sql, $bindings);
         */

        $where = " WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
                . "AND category IN (" . implode(", ", $keys_array) . ") "
                . "AND user = :user ";


        $sql = "SELECT SUM(sum) FROM ( ";
        $sql .= "   SELECT value*multiplier as sum FROM " . $this->getTable() . " " . $where . " AND unit = 'month' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value*(4*multiplier) as sum FROM " . $this->getTable() . " " . $where . " AND unit = 'week' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value*(30*multiplier) as sum FROM " . $this->getTable() . " " . $where . " AND unit = 'day' ";
        $sql .= "   UNION ALL ";
        $sql .= "   SELECT value/(12*multiplier) as sum FROM " . $this->getTable() . " " . $where . " AND unit = 'year' ";
        $sql .= ") f";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

}
