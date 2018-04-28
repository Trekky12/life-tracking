<?php

namespace App\Fuel;

class Mapper extends \App\Base\Mapper {

    protected $table = 'fuel';
    protected $model = '\App\Fuel\FuelEntry';
    protected $filterByUser = false;
    protected $insertUser = false;

    public function getLastMileage($id, $mileage, $car) {
        $sql = "SELECT mileage FROM " . $this->getTable() . "  "
                . "WHERE id != :id "
                . " AND mileage IS NOT NULL "
                . " AND mileage <= :mileage"
                . " AND volume IS NOT NULL "
                . " AND total_price IS NOT NULL "
                . " AND car = :car ";


        $bindings = array("id" => $id, "mileage" => $mileage, "car" => $car);

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

    public function getLastFull($id, $mileage, $car) {
        $sql = "SELECT * FROM " . $this->getTable() . "  "
                . "WHERE id != :id "
                . " AND mileage IS NOT NULL "
                . " AND mileage <= :mileage "
                . " AND volume IS NOT NULL "
                . " AND total_price IS NOT NULL "
                . " AND type = 1 "
                . " AND type IS NOT NULL"
                . " AND car = :car ";

        $bindings = array("id" => $id, "mileage" => $mileage, "car" => $car);

        $sql .= " ORDER BY mileage DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }

    public function getVolume($car, $date, $lastDate) {
        $sql = "SELECT SUM(volume) FROM " . $this->getTable() . " "
                . "WHERE car = :car "
                . " AND date <= :date "
                . " AND date > :lastDate ";

        $bindings = array("car" => $car, "date" => $date, "lastDate" => $lastDate);

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

        if (empty($user_cars)) {
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
        if (empty($user_cars)) {
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

    public function minMileage() {
        /**
         * @see https://stackoverflow.com/a/15292049
         */
        $sql = "SELECT f1.car, f1.date, f1.mileage "
                . "FROM " . $this->getTable() . " f1 "
                . "INNER JOIN "
                . "(  "
                . "SELECT car, MIN(date) minDate From " . $this->getTable() . " GROUP BY car "
                . ") f2 "
                . "ON f1.car=f2.car "
                . "WHERE f1.date=f2.minDate";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();


        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key]["date"] = $row["date"];
            $results[$key]["mileage"] = $row["mileage"];
        }
        return $results;
    }

    public function sumMileageInterval($car, $date) {
        $sql = "SELECT MAX(mileage) as max, :date as start, DATE_ADD(:date, INTERVAL 1 YEAR) as end"
                . " FROM " . $this->getTable() . " "
                . " WHERE car = :car "
                . " AND date <= DATE_ADD(:date, INTERVAL 1 YEAR)"
                . " LIMIT 1";

        $bindings = array(
            "car" => $car,
            "date" => $date
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetch(\PDO::FETCH_BOTH);
    }

    public function getTotalMileage() {
        $sql = "SELECT car, MIN(mileage) as min, MAX(mileage) as max, MAX(mileage) - MIN(mileage) as diff FROM " . $this->getTable() . " "
                . " GROUP BY car";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();


        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = $row;
        }
        return $results;
    }

}
