<?php

namespace App\Domain\Trips\Route;

class RouteMapper extends \App\Domain\Mapper {

    protected $table = "trips_route";
    protected $dataobject = \App\Domain\Trips\Route\Route::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getFromTrip($id, $from = null, $to = null, $order = null) {
        $bindings = array("id" => $id);

        $sql = "SELECT id, name, start_date, end_date, profile FROM " . $this->getTableName() . " WHERE trip = :id ";

        if (!is_null($from) && !is_null($to)) {
            $sql .= " AND ( start_date = :from OR end_date = :from OR (:from BETWEEN start_date AND end_date) OR (:to BETWEEN start_date AND end_date)) ";
            $bindings["from"] = $from;
            $bindings["to"] = $to;
        }

        if (!is_null($order)) {
            $sql .= " ORDER BY {$order}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }

}
