<?php

namespace App\Domain\Workouts\Exercise;

class ExerciseMapper extends \App\Domain\Mapper {

    protected $table = "workouts_exercises";
    protected $dataobject = \App\Domain\Workouts\Exercise\Exercise::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function deleteMusclesGroups($exercise_id, $primary = true) {
        $sql = "DELETE FROM " . $this->getTableName("workouts_exercises_muscles") . "  WHERE exercise = :exercise and is_primary = :is_primary";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "exercise" => $exercise_id,
            "is_primary" => $primary ? 1 : 0
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addMuscleGroups($exercise_id, $muscles = array(), $primary = true) {

        $data_array = array();
        $keys_array = array();
        foreach ($muscles as $idx => $muscles) {
            $data_array["exercise" . $idx] = $exercise_id;
            $data_array["muscle" . $idx] = $muscles;
            $data_array["is_primary" . $idx] = $primary ? 1 : 0;
            $keys_array[] = "(:exercise" . $idx . " , :muscle" . $idx . ", :is_primary" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("workouts_exercises_muscles") . " (exercise, muscle, is_primary) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getMuscleGroups($exercise_id, $primary = true) {
        $sql = "SELECT muscle FROM " . $this->getTableName("workouts_exercises_muscles") . " WHERE exercise = :exercise and is_primary = :is_primary";

        $bindings = [
            "exercise" => $exercise_id,
            "is_primary" => $primary ? 1 : 0
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

}
