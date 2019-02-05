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

}
