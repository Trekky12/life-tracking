<?php

namespace App\Domain\Car\Service;

class CarServiceMapper extends \App\Domain\Mapper {

    protected $table = 'cars_service';
    protected $dataobject = \App\Domain\Car\Service\CarServiceEntry::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getLastMileage($id, $mileage, $car) {
        $sql = "SELECT mileage FROM " . $this->getTableName() . "  "
            . "WHERE id != :id "
            . " AND mileage IS NOT NULL "
            . " AND mileage <= :mileage"
            . " AND fuel_volume IS NOT NULL "
            //. " AND fuel_total_price IS NOT NULL "
            . " AND car = :car "
            . " AND type = :type ";


        $bindings = array("id" => $id, "mileage" => $mileage, "car" => $car, "type" => 0);

        $sql .= " ORDER BY mileage DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }

        return null;
    }

    public function setDistance($id, $lastMileage = 0) {
        $sql = "UPDATE " . $this->getTableName() . " SET fuel_distance  = mileage - :mileage WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'mileage' => $lastMileage,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getLastFull($id, $mileage, $car) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  "
            . "WHERE id != :id "
            . " AND mileage IS NOT NULL "
            . " AND mileage <= :mileage "
            . " AND fuel_volume IS NOT NULL "
            //. " AND fuel_total_price IS NOT NULL "
            . " AND fuel_type = 1 "
            . " AND fuel_type IS NOT NULL"
            . " AND car = :car "
            . " AND type = :type ";

        $bindings = array("id" => $id, "mileage" => $mileage, "car" => $car, "type" => 0);

        $sql .= " ORDER BY mileage DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function getVolume($car, $date, $lastDate) {
        $sql = "SELECT SUM(fuel_volume) FROM " . $this->getTableName() . " "
            . "WHERE car = :car "
            . " AND date <= :date "
            . " AND date > :lastDate "
            . " AND type = :type ";

        $bindings = array("car" => $car, "date" => $date, "lastDate" => $lastDate, "type" => 0);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return floatval($stmt->fetchColumn());
        } else {
            throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
        }
    }

    public function setConsumption($id, $consumption = 0) {
        $sql = "UPDATE " . $this->getTableName() . " SET fuel_consumption  = :consumption WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'consumption' => $consumption,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getAllOfType($car_id, $sorted = false, $limit = false, $type = 0) {

        $sql = "SELECT * FROM " . $this->getTableName();

        $bindings = array("type" => $type, "car" => $car_id);

        $sql .= " WHERE car = :car ";
        $sql .= " AND type = :type ";

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
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function minMileage($car_id) {
        $sql = "SELECT date, mileage "
            . "FROM " . $this->getTableName() . " f "
            . "WHERE f.car = :car AND mileage is not null "
            . "ORDER BY date ASC "
            . "LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["car" => $car_id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function sumMileageInterval($car_id, $date) {
        $sql = "SELECT MAX(mileage) as max, :date as start, DATE_ADD(:date, INTERVAL 1 YEAR) as end"
            . " FROM " . $this->getTableName() . " "
            . " WHERE car = :car "
            . " AND date <= DATE_ADD(:date, INTERVAL 1 YEAR)"
            . " LIMIT 1";

        $bindings = array(
            "car" => $car_id,
            "date" => $date
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetch(\PDO::FETCH_BOTH);
    }

    public function getTotalMileage($car_id, $startdate = null) {
        $sql = "SELECT MIN(mileage) as min, MAX(mileage) as max, MAX(mileage) - c.mileage_start as diff "
            . "FROM " . $this->getTableName() . " cs,  " . $this->getTableName("cars") . " c "
            . "WHERE c.id = cs.car "
            . "AND cs.car = :car";

        if (!is_null($startdate)) {
            $sql .= " AND cs.date >= c.mileage_start_date ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["car" => $car_id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getTableSQL($select, $searchQuery, $car_id, $type) {

        $bindings = [
            "searchQuery" => $searchQuery,
            "type" => $type,
            "car" => $car_id
        ];

        $sql = "SELECT {$select} "
            . " FROM " . $this->getTableName() . " cs INNER JOIN " . $this->getTableName('cars') . " c "
            . " ON cs.car = c.id "
            . " WHERE "
            . " ("
            . " cs.date LIKE :searchQuery OR "
            . " cs.mileage LIKE :searchQuery OR "
            . " cs.fuel_price LIKE :searchQuery OR "
            . " c.name LIKE :searchQuery OR "
            . " cs.fuel_volume LIKE :searchQuery OR "
            . " cs.fuel_total_price LIKE :searchQuery OR"
            . " cs.fuel_consumption LIKE :searchQuery OR"
            . " cs.fuel_location LIKE :searchQuery OR"
            . " cs.notice LIKE :searchQuery "
            . ")"
            . " AND c.id = :car "
            . " AND type = :type ";

        return [$sql, $bindings];
    }

    public function tableCount($car_id, $type = 0, $searchQuery = "%") {

        list($sql, $bindings) = $this->getTableSQL("COUNT(cs.id)", $searchQuery, $car_id, $type);

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableDataFuel($car_id, $sortColumn = "changedOn", $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $type = 0;
        $sort = "date";
        switch ($sortColumn) {
            case 0:
                $sort = "date";
                break;
            case 1:
                $sort = "mileage";
                break;
            case 2:
                $sort = "fuel_price";
                break;
            case 3:
                $sort = "fuel_volume";
                break;
            case 4:
                $sort = "fuel_total_price";
                break;
            case 5:
                $sort = "fuel_type";
                break;
            case 6:
                $sort = "fuel_consumption";
                break;
            case 7:
                $sort = "fuel_location";
                break;
        }

        $partly = $this->translation->getTranslatedString("FUEL_PARTLY");
        $full = $this->translation->getTranslatedString("FUEL_FULL");

        $select = "cs.date, cs.mileage, cs.fuel_price, cs.fuel_volume, cs.fuel_total_price, "
            . "CASE "
            . " WHEN cs.fuel_volume > 0 AND cs.fuel_type = 0 THEN '{$partly}' "
            . " WHEN cs.fuel_volume > 0 AND cs.fuel_type = 1 THEN '{$full}'"
            . " ELSE '' END as fuel_type, "
            . "cs.fuel_consumption, cs.fuel_location, "
            . "cs.id, cs.id";

        list($sql, $bindings) = $this->getTableSQL($select, $searchQuery, $car_id, $type);

        $sql .= " ORDER BY {$sort} {$sortDirection}, cs.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    public function getTableDataService($car_id, $sortColumn = "changedOn", $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $type = 1;
        $sort = "date";
        switch ($sortColumn) {
            case 0:
                $sort = "date";
                break;
            case 1:
                $sort = "mileage";
                break;
            case 2:
                $sort = "oil";
                break;
            case 3:
                $sort = "water_wiper";
                break;
            case 4:
                $sort = "air";
                break;
            case 5:
                $sort = "tire_change";
                break;
            case 6:
                $sort = "garage";
                break;
        }

        $select = "cs.date, cs.mileage, "
            . "CASE "
            . " WHEN cs.service_oil_before > 0 OR cs.service_oil_after > 0 "
            . " THEN 'x'  "
            . " ELSE '' "
            . "END as oil, "
            . "CASE "
            . " WHEN cs.service_water_wiper_before > 0 OR cs.service_water_wiper_after > 0 "
            . " THEN 'x'  "
            . " ELSE '' "
            . "END as water_wiper, "
            . "CASE "
            . " WHEN cs.service_air_front_left_before > 0 OR cs.service_air_front_left_after > 0 OR "
            . "      cs.service_air_front_right_before > 0 OR cs.service_air_front_right_after > 0 OR "
            . "      cs.service_air_back_left_before > 0 OR cs.service_air_back_left_after > 0 OR "
            . "      cs.service_air_back_right_before > 0 OR cs.service_air_back_right_after > 0 "
            . " THEN 'x'  "
            . " ELSE '' "
            . "END as air, "
            . "CASE "
            . " WHEN cs.service_tire_change > 0 "
            . " THEN 'x'  "
            . " ELSE '' "
            . "END as tire_change, "
            . "CASE "
            . " WHEN cs.service_garage > 0 "
            . " THEN 'x'  "
            . " ELSE '' "
            . "END as garage, "
            . "cs.id, cs.id";

        list($sql, $bindings) = $this->getTableSQL($select, $searchQuery, $car_id, $type);


        $sql .= " ORDER BY {$sort} {$sortDirection}, cs.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    public function getMarkers($from, $to, $user_cars = []) {

        if (empty($user_cars)) {
            return [];
        }

        $bindings = ["from" => $from, "to" => $to];

        $car_bindings = array();
        foreach ($user_cars as $idx => $car) {
            $car_bindings[":car_" . $idx] = $car;
        }

        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE date >= :from AND date <= :to AND lat IS NOT NULL AND lng IS NOT NULL ";
        $sql .= " AND car IN (" . implode(',', array_keys($car_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($bindings, $car_bindings));

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }
}
