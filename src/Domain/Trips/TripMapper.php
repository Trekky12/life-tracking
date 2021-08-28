<?php

namespace App\Domain\Trips;

class TripMapper extends \App\Domain\Mapper {

    protected $table = "trips";
    protected $dataobject = \App\Domain\Trips\Trip::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "trips_user";
    protected $element_name = "trip";

    public function getTripsOfUser($filter = "") {
        $sql2 = "SELECT t.*, "
                . "CASE "
                . " WHEN MIN(start_date) IS NOT NULL THEN MIN(start_date) "
                . " ELSE MIN(end_date) "
                . "END as min_date, "
                . "CASE "
                . " WHEN MAX(end_date) IS NOT NULL THEN MAX(end_date) "
                . " ELSE MAX(start_date) "
                . "END as max_date "
                . " FROM " . $this->getTableName() . " t LEFT JOIN " . $this->getTableName($this->user_table) . " tu "
                . " ON t.id = tu.trip "
                . " LEFT JOIN " . $this->getTableName("trips_event") . " e "
                . " ON t.id = e.trip "
                . " WHERE tu.user = :user OR t.user = :user "
                . " GROUP BY t.id"
                . " ORDER BY t.createdOn DESC, name";

        switch ($filter) {
            case "past":
                $sql = "SELECT l.* FROM (" . $sql2 . ") as l WHERE min_date < CURDATE() AND max_date < CURDATE()";
                break;
            case "plan":
                $sql = "SELECT l.* FROM (" . $sql2 . ") as l WHERE min_date IS NULL AND max_date IS NULL";
                break;
            default:
                $sql = "SELECT l.* FROM (" . $sql2 . ") as l"; // WHERE min_date >= CURDATE() AND max_date <= CURDATE()";
                break;
        }

        $bindings = [
            "user" => $this->user_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            //$row["min_date"] = !is_null($row["start_min"]) ? $row["start_min"] : $row["end_min"];
            //$row["max_date"] = !is_null($row["end_max"]) ? $row["end_max"] : $row["start_max"];

            $results[] = new $this->dataobject($row);
        }
        return $results;
    }

}
