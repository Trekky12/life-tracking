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

    public function getAllPlans($sorted, $is_template = false, $archive = null) {
        $sql = "SELECT p.id, p.*, "
            . "COUNT(CASE WHEN pe.type = 'day' THEN 1 END) AS days,"
            . "COUNT(CASE WHEN pe.type = 'exercise' THEN 1 END) AS exercises "
            . " FROM " . $this->getTableName() . " p LEFT JOIN " . $this->getTableName("workouts_plans_exercises") . " pe ON p.id = pe.plan "
            . " WHERE is_template = :is_template ";
        //$sql = "SELECT * FROM " . $this->getTableName() . "  WHERE is_template = :is_template";

        $bindings = [
            "is_template" => $is_template ? 1 : 0,
        ];
        if (!$is_template) {
            $this->addSelectFilterForUser($sql, $bindings, "p.");
        }

        if (!is_null($archive) && strlen($archive) > 0) {
            $sql .= " AND archive = :archive ";
            $bindings["archive"] = $archive;
        }

        $sql .= " GROUP BY p.id ";

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

    public function deleteExercises($plan_id, $exercises = []) {
        $sql = "DELETE FROM " . $this->getTableName("workouts_plans_exercises") . "  WHERE plan = :plan";

        $bindings = [
            "plan" => $plan_id
        ];

        if (!empty($exercises)) {
            $keys_array = [];
            foreach ($exercises as $idx => $exercise) {
                $bindings["exercise" . $idx] = $exercise;
                $keys_array[] = ":exercise" . $idx;
            }
            $sql .= " AND id IN (" . implode(',', $keys_array) . ")";
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addExercises($plan_id, $exercises = []) {

        $data_array = [];
        $keys_array = [];
        foreach ($exercises as $idx => $exercise) {
            $data_array["plan" . $idx] = $plan_id;
            $data_array["exercise" . $idx] = $exercise["exercise"];
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

    public function updateExercises($plan_id, $exercises = []) {

        $sql = "UPDATE " . $this->getTableName("workouts_plans_exercises") . "
            SET exercise = :exercise,
                position = :position,
                sets = :sets,
                type = :type,
                notice = :notice,
                is_child = :is_child
            WHERE id = :id AND plan = :plan";

        $stmt = $this->db->prepare($sql);

        foreach ($exercises as $exercise) {
            $result = $stmt->execute([
                ':id'       => $exercise['id'],
                ':plan'     => $plan_id,
                ':exercise' => $exercise['exercise'],
                ':position' => $exercise['position'],
                ':sets'     => json_encode($exercise['sets']),
                ':type'     => $exercise['type'],
                ':notice'   => $exercise['notice'],
                ':is_child' => $exercise['is_child'],
            ]);

            if (!$result) {
                throw new \Exception(
                    $this->translation->getTranslatedString('UPDATE_FAILED')
                );
            }
        }

        return true;
    }

    public function getExercises($plan_id) {
        $sql = "SELECT id, exercise, sets, type, notice, is_child FROM " . $this->getTableName("workouts_plans_exercises") . " WHERE plan = :plan ORDER BY position, createdOn";

        $bindings = [
            "plan" => $plan_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["id"]] = [
                "id" => $row["id"],
                "exercise" => !is_null($row["exercise"]) ? intval($row["exercise"]) : null,
                "sets" => json_decode($row["sets"], true),
                "type" => $row["type"],
                "notice" => $row["notice"],
                "is_child" => $row["is_child"],
                "plans_exercises_id" => $row["id"]
            ];
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
