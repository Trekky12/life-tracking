<?php

namespace App\Fuel;

class Mapper extends \App\Base\Mapper {

    protected $table = 'fuel';
    protected $model = '\App\Fuel\FuelEntry';
    protected $filterByUser = false;
    protected $insertUser = true;

    public function getLastMileage($id, $mileage) {
        $sql = "SELECT mileage FROM " . $this->getTable() . "  "
                . "WHERE id != :id "
                . " AND mileage IS NOT NULL "
                . " AND mileage <= :mileage"
                . " AND volume IS NOT NULL "
                . " AND total_price IS NOT NULL ";


        $bindings = array("id" => $id, "mileage" => $mileage);
        $this->filterByUser($sql, $bindings);

        $sql .= " ORDER BY mileage DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }

        return null;
    }

    public function setDistance($id, $lastMileage = 0) {
        $sql = "UPDATE " . $this->getTable() . " SET distance  = mileage - :mileage WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'mileage' => $lastMileage,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getLastFull($id, $mileage) {
        $sql = "SELECT * FROM " . $this->getTable() . "  "
                . "WHERE id != :id "
                . " AND mileage IS NOT NULL "
                . " AND mileage <= :mileage "
                . " AND volume IS NOT NULL "
                . " AND total_price IS NOT NULL "
                . " AND type = 1 "
                . " AND type IS NOT NULL";

        $bindings = array("id" => $id, "mileage" => $mileage);
        $this->filterByUser($sql, $bindings);

        $sql .= " ORDER BY mileage DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }

    public function getVolume($date, $lastDate) {
        $sql = "SELECT SUM(volume) FROM " . $this->getTable() . " "
                . "WHERE date <= :date "
                . " AND date > :lastDate ";

        $bindings = array("date" => $date, "lastDate" => $lastDate);
        $this->filterByUser($sql, $bindings);


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return floatval($stmt->fetchColumn());
        } else {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
        }
    }

    public function setConsumption($id, $consumption = 0) {
        $sql = "UPDATE " . $this->getTable() . " SET consumption  = :consumption WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'consumption' => $consumption,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getAllofCars($sorted = false, $limit = false, $user_cars = array()) {
        
        if(empty($user_cars)){
            return [];
        }
        $sql = "SELECT * FROM " . $this->getTable();

        $bindings = array();
        foreach ($user_cars as $idx => $car) {
            $bindings[":car_" . $idx] = $car;
        }

        $sql .= " WHERE car IN (" . implode(',', array_keys($bindings)) . ")";

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        if ($limit && !is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function countwithCars($user_cars) {
        if(empty($user_cars)){
            return 0;
        }
        $sql = "SELECT COUNT({$this->id}) FROM " . $this->getTable();

        $bindings = array();
        foreach ($user_cars as $idx => $car) {
            $bindings[":car_" . $idx] = $car;
        }

        $sql .= " WHERE car IN (" . implode(',', array_keys($bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_DATA'));
    }

}
