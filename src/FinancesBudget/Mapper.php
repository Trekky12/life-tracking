<?php

namespace App\FinancesBudget;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_budgets';
    protected $model = '\App\FinancesBudget\Budget';

    public function getSum() {
        $sql = "SELECT SUM(value) as sum FROM " . $this->getTable() . " ";

        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

    public function getBudgets() {
        $sql = "SELECT fb.*, SUM(f.value) as sum, ROUND(((SUM(f.value)/fb.value)*100),2) as percent, fb.value-SUM(f.value) as diff 
            FROM " . $this->getTable() . " as fb, " . $this->getTable("finances") . " f, " . $this->getTable("finances_budgets_categories") . " fc
            WHERE f.category = fc.category AND fc.budget = fb.id 
            AND MONTH(date) = MONTH(CURRENT_DATE())
            AND YEAR(date) = YEAR(CURRENT_DATE()) 
            AND fb.user = :user AND f.user = :user 
            AND f.type = :type
            GROUP BY fb.id";
        
        $bindings = array("user" => $this->userid, "type" => 0);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }
    
    public function hasRemainsBudget() {
        $sql = "SELECT b.id FROM " . $this->getTable() . " b LEFT JOIN " . $this->getTable("finances_budgets_categories") . " bc ON b.id = bc.budget WHERE category IS NULL";
        
        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
    
    public function getRemainsBudget(){
        $sql = "SELECT b.* FROM " . $this->getTable() . " b LEFT JOIN " . $this->getTable("finances_budgets_categories") . " bc ON b.id = bc.budget WHERE category IS NULL";

        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }
    
    public function getRemainsExpenses(){
        $sql = "SELECT SUM(f.value) as sum FROM " . $this->getTable("finances") . " as f 
                WHERE f.category NOT IN (SELECT category FROM ". $this->getTable("finances_budgets_categories") . " )
                AND MONTH(date) = MONTH(CURRENT_DATE())
                AND YEAR(date) = YEAR(CURRENT_DATE())
                AND f.type = :type";
        $bindings = array("type" => 0);
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        return floatval($stmt->fetchColumn());
        
    }
    
    
    
    
    public function deleteCategoriesFromBudget($budget) {
        $sql = "DELETE FROM " . $this->getTable("finances_budgets_categories") . "  WHERE budget = :budget";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "budget" => $budget,
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addCategoriesToBudget($budget, $categories = array()) {
        $data_array = array();
        $keys_array = array();
        foreach ($categories as $idx => $category) {
            $data_array["budget" . $idx] = $budget;
            $data_array["category" . $idx] = $category;
            $keys_array[] = "(:budget" . $idx . " , :category" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTable("finances_budgets_categories") . " (budget, category) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getCategoriesFromBudget($budget) {
        $sql = "SELECT category FROM " . $this->getTable("finances_budgets_categories") . " WHERE budget = :budget";

        $bindings = array("budget" => $budget);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }
    
    
    public function getBudgetCategories() {
        $sql = "SELECT budget, category FROM " . $this->getTable("finances_budgets_categories") . "";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = intval($row["budget"]);
            if (!array_key_exists($key, $results)) {
                $results[$key] = array();
            }
            $results[$key][] = intval($row["category"]);
        }
        return $results;
    }

}
