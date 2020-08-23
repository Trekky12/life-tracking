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
            $keys_array[] = "(:plan" . $idx . " , :exercise" . $idx . ", :position" . $idx . ", :sets" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_plans_exercises") . " (plan, exercise, position, sets) "
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
        $sql = "SELECT exercise, sets FROM " . $this->getTableName("workouts_plans_exercises") . " WHERE plan = :plan ORDER BY position";

        $bindings = [
            "plan" => $plan_id
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
