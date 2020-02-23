<?php

namespace App\Finances\Assignment;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_categories_assignment';
    protected $model = '\App\Finances\Assignment\Assignment';

    public function findMatchingCategory($user_id, $description, $value) {
        
        $bindings = ["description" => trim($description), "value" => floatval($value), "user" => $user_id];
        
        $sql = "SELECT category FROM " . $this->getTableName() . " "
                . " WHERE "
                // same description
                . " (LOWER(description) = LOWER(:description) ) "
                // value in range
                . " AND (( :value >= min_value ) OR min_value IS NULL)"
                . " AND (( :value < max_value ) OR max_value IS NULL)"
                . " AND user =:user LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

}
