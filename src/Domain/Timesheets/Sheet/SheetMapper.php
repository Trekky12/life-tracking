<?php

namespace App\Domain\Timesheets\Sheet;

class SheetMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets";
    protected $dataobject = \App\Domain\Timesheets\Sheet\Sheet::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function set_duration($id, $duration) {
        $sql = "UPDATE " . $this->getTableName() . " SET duration = :duration WHERE id  = :id";
        $bindings = array("id" => $id, "duration" => $duration);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function set_duration_modified($id, $duration_modified) {
        $sql = "UPDATE " . $this->getTableName() . " SET duration_modified = :duration_modified WHERE id  = :id";
        $bindings = array("id" => $id, "duration_modified" => $duration_modified);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getLastSheetWithStartDateToday($project) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  "
                . "WHERE project = :project "
                . " AND end IS NULL "
                . "AND DATE(start) = CURDATE()";

        $bindings = array("project" => $project);

        $sql .= " ORDER BY start DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    /**
     * Table
     */
    private function getTableSQL($select, $categories) {

        $cat_bindings = array();
        foreach ($categories as $idx => $cat) {
            $cat_bindings[":cat_" . $idx] = $cat;
        }

        $sql = "SELECT {$select} FROM " . $this->getTableName() . " t"
                . " LEFT JOIN " . $this->getTableName("timesheets_sheets_categories") . " tcs ON t.id = tcs.sheet"
                . " LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc ON tc.id = tcs.category "
                . " WHERE t.project = :project "
                . " AND ("
                . "     (DATE(t.start) >= :from AND DATE(t.end) <= :to ) OR"
                . "     (DATE(t.start) >= :from AND DATE(t.start) <= :to AND t.end IS NULL ) OR"
                . "     (DATE(t.end) >= :from AND DATE(t.end) <= :to AND t.start IS NULL )"
                . " ) AND ("
                . "     t.start LIKE :searchQuery OR "
                . "     t.end LIKE :searchQuery OR "
                . "     tc.name LIKE :searchQuery "
                . ") ";
        if (!empty($cat_bindings)) {
            $sql .= " AND (tcs.sheet IN ( "
                    . "             SELECT sheet "
                    . "             FROM " . $this->getTableName("timesheets_sheets_categories") . " "
                    . "             WHERE category IN (" . implode(',', array_keys($cat_bindings)) . ")"
                    . "             GROUP BY sheet "
                    . "             HAVING COUNT(sheet) >= " . count($cat_bindings) . " "
                    . ") "
                    . " OR tcs.category is NULL)";
        }
        $sql .= " GROUP BY t.id";
        return [$sql, $cat_bindings];
    }

    public function tableCount($project, $from, $to, $categories, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "project" => $project, "from" => $from, "to" => $to);

        list($tableSQL, $cat_bindings) = $this->getTableSQL("DISTINCT t.id", $categories);

        $sql = "SELECT COUNT(t.id) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function tableSum($project, $from, $to, $categories, $searchQuery = "%", $field = "t.duration") {

        $bindings = array("searchQuery" => $searchQuery, "project" => $project, "from" => $from, "to" => $to);

        list($tableSQL, $cat_bindings) = $this->getTableSQL($field, $categories);

        $sql = "SELECT SUM($field) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($project, $from, $to, $categories, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array(
            "searchQuery" => "%" . $searchQuery . "%",
            "project" => $project,
            "from" => $from,
            "to" => $to);

        $sort = "id";
        switch ($sortColumn) {
            case 0:
                $sort = "IFNULL(DATE(start),DATE(end))";
                break;
            case 1:
                $sort = "start";
                break;
            case 2:
                $sort = "end";
                break;
            case 3:
                $sort = "duration";
                break;
        }

        $select = "DISTINCT "
                . "t.id, "
                . "t.createdBy, "
                . "t.createdOn, "
                . "t.changedBy, "
                . "t.changedOn, "
                . "t.project, "
                . "t.start, "
                . "t.end, "
                . "t.duration, "
                . "t.duration_modified, "
                . "GROUP_CONCAT(tc.name SEPARATOR ', ') as categories";

        list($tableSQL, $cat_bindings) = $this->getTableSQL($select, $categories);

        $sql = $tableSQL;
        $sql .= " ORDER BY {$sort} {$sortDirection}, t.start {$sortDirection}, t.end {$sortDirection}, t.createdOn {$sortDirection}, t.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function deleteCategoriesFromSheet($sheet_id) {
        $sql = "DELETE FROM " . $this->getTableName("timesheets_sheets_categories") . "  WHERE sheet = :sheet";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "sheet" => $sheet_id,
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addCategoriesToSheet($sheet_id, $categories = array()) {
        $data_array = array();
        $keys_array = array();
        foreach ($categories as $idx => $category) {
            $data_array["sheet" . $idx] = $sheet_id;
            $data_array["category" . $idx] = $category;
            $keys_array[] = "(:sheet" . $idx . " , :category" . $idx . ")";
        }

        $sql = "INSERT IGNORE INTO " . $this->getTableName("timesheets_sheets_categories") . " (sheet, category) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getCategoriesFromSheet($sheet_id) {
        $sql = "SELECT category FROM " . $this->getTableName("timesheets_sheets_categories") . " WHERE sheet = :sheet";

        $bindings = array("sheet" => $sheet_id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    public function getCategoriesWithNamesFromSheet($sheet_id) {
        $sql = "SELECT GROUP_CONCAT(tc.name SEPARATOR ', ') "
                . "FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs "
                . " LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc ON tc.id = tcs.category "
                . " WHERE tcs.sheet = :sheet";

        $bindings = array("sheet" => $sheet_id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        return "";
    }

    public function getTimes() {
        $sql = "SELECT s.project, SUM(s.duration) as sum, SUM(s.duration_modified) as sum_modified "
                . " FROM " . $this->getTableName() . " s "
                . " GROUP BY s.project";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[intval($row["project"])] = ["sum" => floatval($row["sum"]), "sum_modified" => floatval($row["sum_modified"])];
        }
        return $results;
    }

    public function addCategoriesToSheets($sheets = [], $categories = []) {

        if (count($sheets) <= 0 || count($categories) <= 0) {
            return;
        }

        $bindings = [];
        $data = [];
        foreach ($sheets as $sheet) {

            $sheet_data = [];
            foreach ($categories as $idx => $category) {
                $bindings["sheet" . $sheet . "_" . $idx] = $sheet;
                $bindings["category" . $sheet . "_" . $idx] = $category;
                $data[] = "(:sheet" . $sheet . "_" . $idx . " , :category" . $sheet . "_" . $idx . ")";
            }
        }

        // IGNORE duplicate keys (do not insert then)
        $sql = "INSERT IGNORE INTO " . $this->getTableName("timesheets_sheets_categories") . " (sheet, category) "
                . "VALUES " . implode(", ", $data) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function removeCategoriesFromSheets($sheets = [], $categories = []) {

        if (count($sheets) <= 0 || count($categories) <= 0) {
            return;
        }

        $bindings = [];
        $data = [];
        foreach ($sheets as $sheet) {

            $sheet_data = [];
            foreach ($categories as $idx => $category) {
                $bindings["sheet" . $sheet . "_" . $idx] = $sheet;
                $bindings["category" . $sheet . "_" . $idx] = $category;
                $data[] = "( sheet = :sheet" . $sheet . "_" . $idx . " AND category = :category" . $sheet . "_" . $idx . ")";
            }
        }

        $sql = "DELETE FROM " . $this->getTableName("timesheets_sheets_categories") . " WHERE " . implode(" OR ", $data) . "";


        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getUsers($id) {
        $sql = "SELECT u.id, u.login "
                . "FROM " . $this->getTableName("timesheets_projects_users") . " project_user,"
                . "" .$this->getTableName() ." sheet, "
                . "" . $this->getTableName("global_users") . " u "
                . "WHERE sheet.id = :id "
                . "AND sheet.project = project_user.project "
                . "AND project_user.user = u.id ";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[intval($row["id"])] = $row["login"];
        }
        return $results;
    }

}
