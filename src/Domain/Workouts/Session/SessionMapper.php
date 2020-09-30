<?php

namespace App\Domain\Workouts\Session;

class SessionMapper extends \App\Domain\Mapper {

    protected $table = "workouts_sessions";
    protected $dataobject = \App\Domain\Workouts\Session\Session::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;
    
    public function getFromPlan($id, $order = null) {
        $bindings = array("id" => $id);

        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE plan = :id ";

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
            $data_array["sets" . $idx] = json_encode($exercise["sets"]);
            $keys_array[] = "(:session" . $idx . " , :exercise" . $idx . ", :sets" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_sessions_exercises") . " (session, exercise, sets) "
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
        $sql = "SELECT exercise, sets FROM " . $this->getTableName("workouts_sessions_exercises") . " WHERE session = :session ORDER BY id";

        $bindings = [
            "session" => $session_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = ["exercise" => intval($row["exercise"]), "sets" => json_decode($row["sets"], true)];
        }
        return $results;
    }

}
