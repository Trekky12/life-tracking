<?php

namespace App\Finances\Budget;

class Mapper extends \App\Base\Mapper {

    protected $table = 'finances_budgets';
    protected $dataobject = \App\Finances\Budget\Budget::class;

    public function getSum() {
        $sql = "SELECT SUM(value) as sum FROM " . $this->getTableName() . " ";

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return intval($stmt->fetchColumn());
    }

    public function getBudgets($sorted = false, $limit = false) {
        $sql = "SELECT fb.*, SUM(f.value) as sum, ROUND(((SUM(f.value)/fb.value)*100),2) as percent, fb.value-SUM(f.value) as diff 
            FROM " . $this->getTableName() . " as fb, " . $this->getTableName("finances") . " f, " . $this->getTableName("finances_budgets_categories") . " fc
            WHERE f.category = fc.category AND fc.budget = fb.id 
            AND MONTH(date) = MONTH(CURRENT_DATE())
            AND YEAR(date) = YEAR(CURRENT_DATE()) 
            AND fb.user = :user AND f.user = :user 
            AND f.type = :type
            GROUP BY fb.id";

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        if ($limit && !is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $bindings = array("user" => $this->user_id, "type" => 0);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function hasRemainsBudget() {
        $sql = "SELECT b.id FROM " . $this->getTableName() . " b LEFT JOIN " . $this->getTableName("finances_budgets_categories") . " bc ON b.id = bc.budget WHERE category IS NULL";

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getRemainsBudget() {
        $sql = "SELECT b.* FROM " . $this->getTableName() . " b LEFT JOIN " . $this->getTableName("finances_budgets_categories") . " bc ON b.id = bc.budget WHERE category IS NULL";

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function getRemainsExpenses() {
        $sql = "SELECT SUM(f.value) as sum FROM " . $this->getTableName("finances") . " as f 
                WHERE f.category NOT IN (SELECT category FROM " . $this->getTableName("finances_budgets_categories") . " )
                AND MONTH(date) = MONTH(CURRENT_DATE())
                AND YEAR(date) = YEAR(CURRENT_DATE())
                AND f.type = :type";
        $bindings = array("type" => 0);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return floatval($stmt->fetchColumn());
    }

    public function deleteCategoriesFromBudget($budget) {
        $sql = "DELETE FROM " . $this->getTableName("finances_budgets_categories") . "  WHERE budget = :budget";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "budget" => $budget,
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
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

        $sql = "INSERT INTO " . $this->getTableName("finances_budgets_categories") . " (budget, category) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getCategoriesFromBudget($budget) {
        $sql = "SELECT category FROM " . $this->getTableName("finances_budgets_categories") . " WHERE budget = :budget";

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
        $sql = "SELECT budget, category FROM " . $this->getTableName("finances_budgets_categories") . "";

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

    public function getBudgetsFromCategory($category) {
        $sql = "SELECT b.* FROM " . $this->getTableName() . " b, " . $this->getTableName("finances_budgets_categories") . " bc "
                . "WHERE b.id = bc.budget AND category = :category ORDER BY b.description ";

        $bindings = array("category" => $category);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function isRemainsBudget($budget) {
        $sql = "SELECT b.id FROM " . $this->getTableName() . " b LEFT JOIN " . $this->getTableName("finances_budgets_categories") . " bc ON b.id = bc.budget "
                . "WHERE category IS NULL "
                . "AND b.id = :budget";

        $bindings = array("budget" => $budget);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

}
