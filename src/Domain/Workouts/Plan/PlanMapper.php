<?php

namespace App\Domain\Workouts\Plan;

class PlanMapper extends \App\Domain\Mapper {

    protected $table = "workouts_plans";
    protected $dataobject = \App\Domain\Workouts\Plan\Plan::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;

    //protected $has_user_table = false;
    //protected $user_table = "timesheets_projects_users";
    //protected $element_name = "project";

    public function getAllPlans($sorted, $is_template = false) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE is_template = :is_template";

        $bindings = [
            "is_template" => $is_template ? 1 : 0,
        ];
        if (!$is_template) {
            $this->addSelectFilterForUser($sql, $bindings);
        }

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getPlan() {
        
    }

    public function deleteExercises($plan_id) {
        $sql = "DELETE FROM " . $this->getTableName("workouts_plans_exercises") . "  WHERE plan = :plan";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "plan" => $plan_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addExercises($plan_id, $exercises = array()) {

        $data_array = array();
        $keys_array = array();
        foreach ($exercises as $idx => $exercise) {
            $data_array["plan" . $idx] = $plan_id;
            $data_array["exercise" . $idx] = $exercise["id"];
            $data_array["position" . $idx] = $exercise["position"];
            $data_array["sets" . $idx] = json_encode($exercise["sets"]);
            $data_array["type" . $idx] = $exercise["type"];
            $data_array["notice" . $idx] = $exercise["notice"];
            $data_array["is_child" . $idx] = $exercise["is_child"];
            $keys_array[] = "(:plan" . $idx . " , :exercise" . $idx . ", :position" . $idx . ", :sets" . $idx . ", :type" . $idx . ", :notice" . $idx . ", :is_child" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_plans_exercises") . " (plan, exercise, position, sets, type, notice, is_child) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getExercises($plan_id) {
        $sql = "SELECT id, exercise, sets, type, notice, is_child FROM " . $this->getTableName("workouts_plans_exercises") . " WHERE plan = :plan ORDER BY position";

        $bindings = [
            "plan" => $plan_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["id"]] = ["exercise" => !is_null($row["exercise"]) ? intval($row["exercise"]) : null, "sets" => json_decode($row["sets"], true), "type" => $row["type"], "notice" => $row["notice"], "is_child" => $row["is_child"]];
        }
        return $results;
    }

    public function getWorkoutDays($plan_id) {
        $sql = "SELECT id, notice FROM " . $this->getTableName("workouts_plans_exercises") . " WHERE plan = :plan AND type = 'day' ORDER BY position";

        $bindings = [
            "plan" => $plan_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["id"]] = $row["notice"];
        }
        return $results;
    }

}
