<?php

namespace App\Domain\Location;

class Mapper extends \App\Domain\Mapper {

    protected $table = 'locations';
    protected $dataobject = \App\Domain\Location\Location::class;

    public function getMarkers($from, $to) {
        $bindings = ["from" => $from, "to" => $to];
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE DATE(createdOn) >= :from AND DATE(createdOn) <= :to";

        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

}
