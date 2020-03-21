<?php

namespace App\Location\Steps;

class Mapper extends \App\Base\Mapper {

    protected $table = 'locations';
    protected $dataobject = \App\Location\Location::class;

    private function getStepsOfDaySQL(&$bindings) {
        $sql = "SELECT DATE(createdOn) as date, MAX(steps) as steps FROM " . $this->getTableName();

        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " GROUP BY DATE(createdOn)";
        $sql .= " ORDER BY DATE(createdOn) DESC";

        return $sql;
    }

    public function getStepsPerYear() {
        $bindings = array();

        $sql_steps = $this->getStepsOfDaySQL($bindings);
        $sql = "SELECT YEAR(temp.date) as year, ROUND(AVG(temp.steps)) as steps, ROUND(MAX(temp.steps)) as max, ROUND(SUM(temp.steps)) as sum "
                . "FROM (" . $sql_steps . ") as temp "
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
                . "FROM (" . $sql_steps . ") as temp "
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
                . "FROM (" . $sql_steps . ") as temp "
                . "WHERE steps > 0 AND YEAR(temp.date) = :year AND MONTH(temp.date) = :month "
                . "ORDER BY temp.date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function getStepsOfDate($date) {
        $bindings = array("date" => $date);

        $sql_steps = $this->getStepsOfDaySQL($bindings);
        $sql = "SELECT ROUND(temp.steps) as steps "
                . "FROM (" . $sql_steps . ") as temp "
                . "WHERE temp.date = :date "
                . "ORDER BY temp.date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchColumn();
    }

    public function updateSteps($date, $steps_old, $steps_new) {
        $bindings = ["date" => $date, "steps_old" => $steps_old, "steps_new" => $steps_new];

        $sql = "UPDATE " . $this->getTableName() . " SET steps=:steps_new WHERE DATE(createdOn) = :date AND (steps >= :steps_old OR steps >= :steps_new)";

        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }

    public function insertSteps($date, $steps_new) {
        $bindings = ["date" => $date, "steps_new" => $steps_new, "user" => $this->user_id];

        $sql = "INSERT INTO " . $this->getTableName() . "(createdOn, user, steps) VALUES(:date, :user, :steps_new)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

}
