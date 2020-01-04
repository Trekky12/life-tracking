<?php

namespace App\Trips\Event;

class Mapper extends \App\Base\Mapper {

    protected $table = "trips_event";
    protected $model = "\App\Trips\Event\Event";
    protected $filterByUser = false;
    protected $insertUser = false;

    public function getFromTrip($id, $from = null, $to = null, $order = null) {
        $bindings = array("id" => $id);

        $sql = "SELECT * FROM " . $this->getTable() . " WHERE trip = :id ";

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
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function getMinMaxEventsDate($id) {
        $sql = "SELECT MIN(start_date) as start_min, MAX(start_date) as start_max, MIN(end_date) as end_min, MAX(end_date) as end_max "
                . " FROM " . $this->getTable() . ""
                . " WHERE trip = :id ";
        $bindings = ["id" => $id];
        $this->filterByUser($sql, $bindings);
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $min = null;
        $max = null;
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $min = !is_null($row["start_min"]) ? $row["start_min"] : $row["end_min"];
            $max = !is_null($row["end_max"]) ? $row["end_max"] : $row["start_max"];
        }
        return array($min, $max);
    }

    public function getMinMaxEventsDates() {
        $bindings = [];
        $sql = "SELECT trip, MIN(start_date) as start_min, MAX(start_date) as start_max, MIN(end_date) as end_min, MAX(end_date) as end_max "
                . " FROM " . $this->getTable() . ""
                . " GROUP BY trip";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $min = null;
            $max = null;
            $min = !is_null($row["start_min"]) ? $row["start_min"] : $row["end_min"];
            $max = !is_null($row["end_max"]) ? $row["end_max"] : $row["start_max"];
            $results[$row["trip"]] = ["min" => $min, "max" => $max];
        }
        return $results;
    }

    public function update_image($id, $image) {
        $sql = "UPDATE " . $this->getTable() . " SET image=:image WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "image" => $image,
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function updatePosition($id, $position, $user) {
        $sql = "UPDATE " . $this->getTable() . " SET position=:position, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "position" => $position,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
