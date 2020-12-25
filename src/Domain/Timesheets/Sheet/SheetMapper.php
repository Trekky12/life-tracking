<?php

namespace App\Domain\Timesheets\Sheet;

class SheetMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets";
    protected $dataobject = \App\Domain\Timesheets\Sheet\Sheet::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function set_diff($id, $diff) {
        $sql = "UPDATE " . $this->getTableName() . " SET diff = :diff WHERE id  = :id";
        $bindings = array("id" => $id, "diff" => $diff);
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
    private function getTableSQL($select) {
        $sql = "SELECT DISTINCT {$select} FROM " . $this->getTableName() . " t"
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
        return $sql;
    }

    public function tableCount($project, $from, $to, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "project" => $project, "from" => $from, "to" => $to);

        $sql = $this->getTableSQL("COUNT(DISTINCT t.id)");

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function tableSum($project, $from, $to, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "project" => $project, "from" => $from, "to" => $to);

        $sql = $this->getTableSQL("SUM(t.diff)");

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($project, $from, $to, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array("searchQuery" => "%" . $searchQuery . "%", "project" => $project, "from" => $from, "to" => $to);

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
                $sort = "diff";
                break;
        }

        $select = "t.*, GROUP_CONCAT(tc.name SEPARATOR ', ') as categories";
        $sql = $this->getTableSQL($select);
        
        $sql .= " GROUP BY t.id";

        $sql .= " ORDER BY {$sort} {$sortDirection}, t.createdOn {$sortDirection}, t.id {$sortDirection}";
        
        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
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

        $sql = "INSERT INTO " . $this->getTableName("timesheets_sheets_categories") . " (sheet, category) "
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

}
