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

}
