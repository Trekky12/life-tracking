<?php

namespace App\Domain\Workouts\Session;

class SessionMapper extends \App\Domain\Mapper {

    protected $table = "workouts_sessions";
    protected $dataobject = \App\Domain\Workouts\Session\Session::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;
    
    public function getFromPlan($id, $order = null) {
        $bindings = array("id" => $id);

        $sql = "SELECT DISTINCT s.*, GROUP_CONCAT(CASE WHEN se.type = 'day' THEN se.notice ELSE NULL END SEPARATOR ', ') as days "
                . " FROM " . $this->getTableName() . " s LEFT JOIN " . $this->getTableName("workouts_sessions_exercises") . " se ON s.id = se.session "
                . " WHERE plan = :id "
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
            $data_array["plans_exercises_id" . $idx] = $exercise["plans_exercises_id"];
            $keys_array[] = "(:session" . $idx . " , :exercise" . $idx . ", :position" . $idx . ", :sets" . $idx . ", :type" . $idx . ", :notice" . $idx . ", :is_child" . $idx . ", :plans_exercises_id" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_sessions_exercises") . " (session, exercise, position, sets, type, notice, is_child, plans_exercises_id) "
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
        $sql = "SELECT exercise, sets, type, notice, is_child, plans_exercises_id FROM " . $this->getTableName("workouts_sessions_exercises") . " WHERE session = :session ORDER BY position";

        $bindings = [
            "session" => $session_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                "exercise" => !is_null($row["exercise"]) ? intval($row["exercise"]) : null, 
                "sets" => json_decode($row["sets"], true), 
                "type" => $row["type"], 
                "notice" => $row["notice"], 
                "is_child" => $row["is_child"], 
                "plans_exercises_id" => $row["plans_exercises_id"],
                "is_finished" => 1
            ];
        }
        return $results;
    }
    
    public function getAllSessionExercises($plan_id = null) {
        $bindings = [
            "type" => "exercise"
        ];
        
        $sql = "SELECT se.exercise, se.sets, s.date"
                . " FROM " . $this->getTableName() . " s, " . $this->getTableName("workouts_sessions_exercises") . " se"
                . " WHERE s.id = se.session AND se.type = :type AND se.exercise IS NOT NULL";

        if(!is_null($plan_id)){
            $sql .= " AND s.plan = :plan ";

            $bindings["plan"] = $plan_id;
        }

        $this->addSelectFilterForUser($sql, $bindings, "s.");
        $sql.= " ORDER by s.date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        $exercises_max_sets = [];
        while ($row = $stmt->fetch()) {

            $session_date = $row["date"];
            if(!array_key_exists($session_date, $results)){
                $results[$session_date] = [];
            }

            $exercise_id = intval($row["exercise"]);
            
            if(!array_key_exists($exercise_id, $results[$session_date])){
                $results[$session_date][$exercise_id] = [];
            }

            $sets = json_decode($row["sets"], true);
            $results[$session_date][$exercise_id] = $sets;


            /**
             * Count maximum sets per exercise
             */
            if(!array_key_exists($exercise_id, $exercises_max_sets)){
                $exercises_max_sets[$exercise_id] = 0;
            }
            if(count($sets) > $exercises_max_sets[$exercise_id]){
                $exercises_max_sets[$exercise_id] = count($sets);
            }
            
        }
        return [$results, $exercises_max_sets];
    }

    public function getMinMaxSessionsDate($plan_id) {
        $sql = "SELECT DATE_SUB(MIN(date), INTERVAL 1 DAY) as start, DATE_ADD(MAX(date), INTERVAL 1 DAY) as end "
                . " FROM " . $this->getTableName() . ""
                . " WHERE plan = :plan ";
        
        $bindings = ["plan" => $plan_id];
        $this->addSelectFilterForUser($sql, $bindings);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $start = null;
        $end = null;
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $start = $row["start"];
            $end = $row["end"];
        }
        return array($start, $end);
    }

}
