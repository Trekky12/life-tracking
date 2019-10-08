<?php

namespace App\Location;

class Mapper extends \App\Base\Mapper {

    protected $table = 'locations';
    protected $model = '\App\Location\Location';

    public function getMarkers($from, $to) {
        $bindings = ["from" => $from, "to" => $to];
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE DATE(createdOn) >= :from AND DATE(createdOn) <= :to";

        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }
    
    private function getStepsOfDaySQL(&$bindings){
        $sql = "SELECT DATE(createdOn) as date, MAX(steps) as steps FROM " . $this->getTable();

        $this->filterByUser($sql, $bindings);

        $sql .= " GROUP BY DATE(createdOn)";
        $sql .= " ORDER BY DATE(createdOn) DESC";
        
        return $sql;
    }

    public function getStepsPerYear() {
        $bindings = array();
    
        $sql_steps = $this->getStepsOfDaySQL($bindings);
        $sql = "SELECT YEAR(temp.date) as year, ROUND(AVG(temp.steps)) as steps, ROUND(MAX(temp.steps)) as max, ROUND(SUM(temp.steps)) as sum "
                . "FROM (".$sql_steps.") as temp "
                . "WHERE steps > 0 "
                . "GROUP BY YEAR(temp.date) "
                . "ORDER BY YEAR(temp.date) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }
    
    public function getStepsOfYear($year) {
        $bindings = array("year" => $year);
    
        $sql_steps = $this->getStepsOfDaySQL($bindings);
        $sql = "SELECT YEAR(temp.date) as year, MONTH(temp.date) as month, ROUND(AVG(temp.steps)) as steps, ROUND(MAX(temp.steps)) as max, ROUND(SUM(temp.steps)) as sum "
                . "FROM (".$sql_steps.") as temp "
                . "WHERE steps > 0 AND YEAR(temp.date) = :year "
                . "GROUP BY YEAR(temp.date), MONTH(date) "
                . "ORDER BY YEAR(temp.date) DESC, MONTH(date) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }
    
    public function getStepsOfYearMonth($year, $month) {
        $bindings = array("year" => $year, "month" => $month);
    
        $sql_steps = $this->getStepsOfDaySQL($bindings);
        $sql = "SELECT temp.date as date, ROUND(temp.steps) as steps "
                . "FROM (".$sql_steps.") as temp "
                . "WHERE steps > 0 AND YEAR(temp.date) = :year AND MONTH(temp.date) = :month "
                . "ORDER BY temp.date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }
    
    
    

}
