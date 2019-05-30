<?php

namespace App\Trips\Event;

class Mapper extends \App\Base\Mapper {

    protected $table = "trips_event";
    protected $model = "\App\Trips\Event\Event";
    protected $filterByUser = false;
    protected $insertUser = false;

    public function getFromTrip($id) {
        $bindings = array("id" => $id);
        
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE trip = :id ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

}
