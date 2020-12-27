<?php

namespace App\Domain\Workouts\Session;

class SessionMapper extends \App\Domain\Mapper {

    protected $table = "workouts_sessions";
    protected $dataobject = \App\Domain\Workouts\Session\Session::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;
    
    public function getFromPlan($id, $order = null) {
        $bindings = array("id" => $id);

        $sql = "SELECT DISTINCT s.*, GROUP_CONCAT(se.notice SEPARATOR ', ') as days "
                . " FROM " . $this->getTableName() . " s LEFT JOIN " . $this->getTableName("workouts_sessions_exercises") . " se ON s.id = se.session "
                . " WHERE (se.type = 'day' OR se.type IS NULL ) "
                . " AND plan = :id "
                . " GROUP BY s.id ";

        if (!is_null($order)) {
            $sql .= " ORDER BY {$order}";
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

    public function deleteExercises($session_id) {
        $sql = "DELETE FROM " . $this->getTableName("workouts_sessions_exercises") . "  WHERE session = :session";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "session" => $session_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addExercises($session_id, $exercises = array()) {

        $data_array = array();
        $keys_array = array();
        foreach ($exercises as $idx => $exercise) {
            $data_array["session" . $idx] = $session_id;
            $data_array["exercise" . $idx] = $exercise["id"];
            $data_array["position" . $idx] = $exercise["position"];
            $data_array["sets" . $idx] = json_encode($exercise["sets"]);
            $data_array["type" . $idx] = $exercise["type"];
            $data_array["notice" . $idx] = $exercise["notice"];
            $data_array["is_child" . $idx] = $exercise["is_child"];
            $keys_array[] = "(:session" . $idx . " , :exercise" . $idx . ", :position" . $idx . ", :sets" . $idx . ", :type" . $idx . ", :notice" . $idx . ", :is_child" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_sessions_exercises") . " (session, exercise, position, sets, type, notice, is_child) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getExercises($session_id) {
        $sql = "SELECT exercise, sets, type, notice, is_child FROM " . $this->getTableName("workouts_sessions_exercises") . " WHERE session = :session ORDER BY position";

        $bindings = [
            "session" => $session_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = ["exercise" => !is_null($row["exercise"]) ? intval($row["exercise"]) : null, "sets" => json_decode($row["sets"], true), "type" => $row["type"], "notice" => $row["notice"], "is_child" => $row["is_child"]];
        }
        return $results;
    }

}
