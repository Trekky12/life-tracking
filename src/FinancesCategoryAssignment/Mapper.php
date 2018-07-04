<?php

namespace App\FinancesCategoryAssignment;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_categories_assignment';
    protected $model = '\App\FinancesCategoryAssignment\Assignment';

    public function get_category($description, $value) {
        $sql = "SELECT category FROM " . $this->getTable() . " "
                . " WHERE "
                // same description
                . " (LOWER(description) = LOWER(:description) ) "
                // value in range
                . " AND (( :value >= min_value ) OR min_value IS NULL)"
                . " AND (( :value < max_value ) OR max_value IS NULL)";
                
        $bindings = array("description" => trim($description), "value" => floatval($value));
        $this->filterByUser($sql, $bindings);
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

}
