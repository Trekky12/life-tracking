<?php

namespace App\Location;

class Mapper extends \App\Base\Mapper {

    public function getMarkers($from, $to) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE DATE(dt) >= :from AND DATE(dt) <= :to";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["from" => $from, "to" => $to]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

}
