<?php

namespace App\Domain\Timesheets\ProjectCategoryBudget;

class ProjectCategoryBudgetMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_categorybudgets";
    protected $dataobject = \App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudget::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getFromProject($id, $order = 'name') {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE project = :id ";

        $bindings = array("id" => $id);

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

    public function deleteCategoriesFromCategoryBudget($categorybudget_id) {
        $sql = "DELETE FROM " . $this->getTableName("timesheets_categorybudgets_categories") . "  WHERE categorybudget = :categorybudget";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "categorybudget" => $categorybudget_id,
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addCategoriesToCategoryBudget($categorybudget_id, $categories = array()) {
        $data_array = array();
        $keys_array = array();
        foreach ($categories as $idx => $category) {
            $data_array["categorybudget" . $idx] = $categorybudget_id;
            $data_array["category" . $idx] = $category;
            $keys_array[] = "(:categorybudget" . $idx . " , :category" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("timesheets_categorybudgets_categories") . " (categorybudget, category) "
            . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getCategoriesFromCategoryBudget($categorybudget_id) {
        $sql = "SELECT category FROM " . $this->getTableName("timesheets_categorybudgets_categories") . " WHERE categorybudget = :categorybudget";

        $bindings = array("categorybudget" => $categorybudget_id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    public function getCategoryBudgetCategories() {
        $sql = "SELECT categorybudget, category FROM " . $this->getTableName("timesheets_categorybudgets_categories") . "";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = intval($row["categorybudget"]);
            if (!array_key_exists($key, $results)) {
                $results[$key] = array();
            }
            $results[$key][] = intval($row["category"]);
        }
        return $results;
    }

    /*
      //Search for exact same categories is not used => same categories or more is used
      public function getCategoryBudgets($project_id, $categories = []) {

      $unique_categories = array_unique($categories);
      asort($unique_categories);
      $selected_categories = !empty($categories) ? implode($unique_categories, "|") : "";

      $bindings = array("project" => $project_id);

      $sql = "SELECT budget.main_category_name, budget.main_category, budget.id, budget.name, budget.notice, budget.value, budget.categorization, budget.warning1, budget.warning2, budget.warning3, budget.category_names,
      CASE
      WHEN budget.categorization = 'duration' THEN SUM(sheet.duration)
      WHEN budget.categorization = 'duration_modified' THEN SUM(sheet.duration_modified)
      ELSE COUNT(sheet.id)
      END as sum,
      CASE
      WHEN budget.categorization = 'duration' THEN ROUND(((SUM(sheet.duration)/budget.value)*100),2)
      WHEN budget.categorization = 'duration_modified' THEN ROUND(((SUM(sheet.duration_modified)/budget.value)*100),2)
      ELSE ROUND(((COUNT(sheet.id)/budget.value)*100),2)
      END as percent,
      CASE
      WHEN budget.categorization = 'duration' THEN budget.value-SUM(sheet.duration)
      WHEN budget.categorization = 'duration_modified' THEN budget.value-SUM(sheet.duration_modified)
      ELSE budget.value-COUNT(sheet.id)
      END as diff
      FROM
      (
      SELECT  b.*,
      GROUP_CONCAT(bc.category ORDER BY bc.category SEPARATOR '|') as categories,
      GROUP_CONCAT(c.name ORDER BY c.id SEPARATOR ', ') as category_names,
      main_cat.name as main_category_name
      FROM " . $this->getTableName() . " b
      LEFT JOIN " . $this->getTableName("timesheets_categorybudgets_categories") . " bc ON b.id = bc.categorybudget
      LEFT JOIN " . $this->getTableName("timesheets_categories") . " c ON bc.category = c.id
      LEFT JOIN " . $this->getTableName("timesheets_categories") . " main_cat ON b.main_category = main_cat.id
      WHERE b.project = :project
      GROUP BY b.id
      ) budget
      LEFT JOIN
      (
      SELECT s.*, GROUP_CONCAT(sc.category ORDER BY sc.category SEPARATOR '|') as categories
      FROM " . $this->getTableName("timesheets_sheets") . " s
      LEFT JOIN " . $this->getTableName("timesheets_sheets_categories") . " sc ON s.id = sc.sheet
      GROUP BY s.id
      ) sheet
      ON sheet.categories = budget.categories";

      if (!empty($selected_categories)) {
      $sql .= " WHERE sheet.categories = :selected_categories ";
      $bindings["selected_categories"] = $selected_categories;
      }

      $sql .= " GROUP BY budget.id "
      . " ORDER BY budget.main_category_name, budget.name";

      $stmt = $this->db->prepare($sql);
      $stmt->execute($bindings);

      return $stmt->fetchAll(\PDO::FETCH_BOTH);
      } */

    public function getBudgetForCategories($project_id, $categories = [], $sheet_id = null) {

        $cat_bindings = array();
        foreach ($categories as $idx => $cat) {
            $cat_bindings[":cat_" . $idx] = $cat;
        }

        $sql = "SELECT  b.*, 
                        GROUP_CONCAT(bc.category ORDER BY bc.category SEPARATOR '|') as categories, 
                        GROUP_CONCAT(c.name ORDER BY c.id SEPARATOR ', ') as category_names,
                        GROUP_CONCAT(bc.category ORDER BY bc.category SEPARATOR ', ') as category_ids, 
                        main_cat.name as main_category_name,
                        customer.name as customer_name
                    FROM " . $this->getTableName() . " b 
                        LEFT JOIN " . $this->getTableName("timesheets_categorybudgets_categories") . " bc ON b.id = bc.categorybudget 
                        LEFT JOIN " . $this->getTableName("timesheets_categories") . " c ON bc.category = c.id
                        LEFT JOIN " . $this->getTableName("timesheets_categories") . " main_cat ON b.main_category = main_cat.id
                        LEFT JOIN " . $this->getTableName("timesheets_customers") . " customer ON b.customer = customer.id
                    WHERE b.project = :project AND b.is_hidden <= 0 ";

        if (!empty($cat_bindings)) {
            $sql .= " AND (bc.categorybudget IN ( "
                . "             SELECT categorybudget "
                . "             FROM " . $this->getTableName("timesheets_categorybudgets_categories") . " "
                . "             WHERE category IN (" . implode(',', array_keys($cat_bindings)) . ")"
                . "             GROUP BY categorybudget "
                . "             HAVING COUNT(categorybudget) <= " . count($cat_bindings) . ""
                . "             ) "
                . ")";
        }
        $sql .= " GROUP BY b.id";
        $sql .= " ORDER BY customer_name, main_category_name, b.name";

        $bindings = ["project" => $project_id];

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($bindings, $cat_bindings));

        $results = [];
        while ($row = $stmt->fetch()) {
            $budget = $row;

            $categories = !is_null($budget["category_ids"]) ? explode(",", $budget["category_ids"]) : [];

            $data = $this->getBudget($project_id, $budget, $categories, $sheet_id);

            $results[] = array_merge($budget, $data);
        }
        return $results;
    }

    private function getBudget($project_id, $budget, $categories = [], $sheet_id = null) {
        $cat_bindings = array();
        foreach ($categories as $idx => $cat) {
            $cat_bindings[":cat_" . $idx] = $cat;
        }


        $bindings = ["project" => $project_id];

        $sql = "SELECT  
                CASE 
                    WHEN STRCMP('{$budget["categorization"]}', 'duration') = 0 THEN SUM(sheet.duration) 
                    WHEN STRCMP('{$budget["categorization"]}', 'duration_modified') = 0 THEN SUM(sheet.duration_modified) 
                    ELSE COUNT(sheet.id) 
                 END as sum, 
                 CASE 
                    WHEN STRCMP('{$budget["categorization"]}', 'duration') = 0 THEN ROUND(((SUM(sheet.duration)/{$budget["value"]})*100),2)
                    WHEN STRCMP('{$budget["categorization"]}', 'duration_modified') = 0 THEN ROUND(((SUM(sheet.duration_modified)/{$budget["value"]})*100),2)
                    ELSE ROUND(((COUNT(sheet.id)/{$budget["value"]})*100),2)
                  END as percent, 
                   CASE 
                    WHEN STRCMP('{$budget["categorization"]}', 'duration') = 0 THEN {$budget["value"]}-SUM(sheet.duration)
                    WHEN STRCMP('{$budget["categorization"]}', 'duration_modified') = 0 THEN {$budget["value"]}-SUM(sheet.duration_modified)
                    ELSE {$budget["value"]}-COUNT(sheet.id)
                  END as diff
                  ";

        // sheet in this budget?  => max(sheet.id = ID)
        // @see https://stackoverflow.com/a/14339977
        if (!is_null($sheet_id)) {
            $sql .= ", MAX(sheet.id = {$sheet_id}) as sheet_in_budget ";
        }

        $sql .= "FROM " . $this->getTableName("timesheets_sheets") . " sheet 
                    WHERE sheet.project = :project ";

        if ($budget["start"] && $budget["end"]) {

            $bindings["start"] = $budget["start"];
            $bindings["end"] = $budget["end"];

            $sql .= " AND ("
                . "     (DATE(sheet.start) >= :start AND DATE(sheet.end) <= :end ) OR"
                . "     (DATE(sheet.start) >= :start AND DATE(sheet.start) <= :end AND sheet.end IS NULL ) OR"
                . "     (DATE(sheet.end) >= :start AND DATE(sheet.end) <= :end AND sheet.start IS NULL )) ";
        }

        if (!empty($cat_bindings)) {
            $sql .= " AND sheet.id IN ( "
                . "             SELECT sheet "
                . "             FROM " . $this->getTableName("timesheets_sheets_categories") . " "
                . "             WHERE category IN (" . implode(',', array_keys($cat_bindings)) . ")"
                . "             GROUP BY sheet "
                . "             HAVING COUNT(sheet) >= " . count($cat_bindings) . " "
                . ") ";
        }

        if ($budget["customer"]) {
            $bindings["customer"] = $budget["customer"];
            $sql .= " and sheet.customer = :customer";
        }

        $sql .= " and sheet.is_planned = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($bindings, $cat_bindings));

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
