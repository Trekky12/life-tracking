<?php

namespace App\FinancesMonthly;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_monthly';
    protected $model = '\App\FinancesMonthly\FinancesEntryMonthly';

    public function getMonthlyEntries() {
        $sql = "SELECT * FROM " . $this->getTable() . " "
                . " WHERE "
                // in date range
                . " (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
                // not run this month
                . " AND (MONTH(last_run) != MONTH(CURRENT_DATE()) OR last_run IS NULL)"
                // start day is today
                . " AND ( DAY(start) = DAY(CURRENT_DATE()) OR start IS NULL)";

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
        
        $bindings = array("type" => $type);
        $keys_array = array();
        foreach ($categories as $idx => $category) {
            $bindings["category" . $idx] = $category;
            $keys_array[] = ":category" . $idx . "";
        }


        $sql = "SELECT SUM(value) as sum FROM " . $this->getTable() . " "
                . "WHERE type = :type "
                . "AND (start <= CURDATE() OR start IS NULL) AND ( end >= CURDATE() OR end IS NULL) "
                . "AND category IN (" . implode(", ", $keys_array) . ") ";

        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

}
