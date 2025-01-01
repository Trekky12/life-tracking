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
    private function getTableSQL($select, $categories, $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null, $group_by = 't.id') {
        $cat_bindings = array();
        if (!is_null($categories)) {
            foreach ($categories as $idx => $cat) {
                $cat_bindings[":cat_" . $idx] = $cat;
            }
        }

        $sql = "SELECT {$select} FROM " . $this->getTableName() . " t"
            . " LEFT JOIN " . $this->getTableName("timesheets_sheets_categories") . " tcs ON t.id = tcs.sheet"
            . " LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc ON tc.id = tcs.category "
            . " LEFT JOIN " . $this->getTableName("timesheets_customers") . " tcus ON tcus.id = t.customer "
            . " WHERE t.project = :project "
            . " AND ("
            . "     (DATE(t.start) >= :from AND DATE(t.end) <= :to ) OR"
            . "     (DATE(t.start) >= :from AND DATE(t.start) <= :to AND t.end IS NULL ) OR"
            . "     (DATE(t.end) >= :from AND DATE(t.end) <= :to AND t.start IS NULL )"
            . " ) AND ("
            . "     t.start LIKE :searchQuery OR "
            . "     t.end LIKE :searchQuery OR "
            . "     tcus.name LIKE :searchQuery OR "
            . "     (tcs.sheet IN ( "
            . "              SELECT tcs2.sheet "
            . "                 FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs2 "
            . "                 LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc2 ON tc2.id = tcs2.category "
            . "                 WHERE tc2.name LIKE :searchQuery "
            . "             ) "
            . "     ) "
            . ")";

        if (!empty($cat_bindings)) {
            $sql .= " AND (tcs.sheet IN ( "
                . "             SELECT sheet "
                . "             FROM " . $this->getTableName("timesheets_sheets_categories") . " "
                . "             WHERE category IN (" . implode(',', array_keys($cat_bindings)) . ")"
                . "             GROUP BY sheet "
                . "             HAVING COUNT(sheet) >= " . count($cat_bindings) . " "
                . ") ";

            if ($include_empty_categories) {
                $sql .= " OR tcs.category is NULL";
            }

            $sql .= " )";
        }

        if (!is_null($billed)) {
            $sql .= " AND t.is_billed = :billed ";
        }

        if (!is_null($payed)) {
            $sql .= " AND t.is_payed = :payed ";
        }

        if (!is_null($planned)) {
            $sql .= " AND t.is_planned = :planned ";
        }

        if (!is_null($customer)) {
            $sql .= " AND t.customer = :customer ";
        }

        $sql .= " GROUP BY {$group_by}";
        return [$sql, $cat_bindings];
    }

    public function tableCount($project, $from, $to, $categories, $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null, $searchQuery = "%") {

        $bindings = array(
            "searchQuery" => $searchQuery,
            "project" => $project,
            "from" => $from,
            "to" => $to
        );

        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($planned)) {
            $bindings["planned"] = $planned;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        list($tableSQL, $cat_bindings) = $this->getTableSQL("DISTINCT t.id", $categories, $include_empty_categories, $billed, $payed, $planned, $customer);

        $sql = "SELECT COUNT(t.id) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function tableSum($project, $from, $to, $categories, $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null, $searchQuery = "%", $field = "t.duration") {

        $bindings = array(
            "searchQuery" => $searchQuery,
            "project" => $project,
            "from" => $from,
            "to" => $to
        );

        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($planned)) {
            $bindings["planned"] = $planned;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        list($tableSQL, $cat_bindings) = $this->getTableSQL($field, $categories, $include_empty_categories, $billed, $payed, $planned, $customer);

        $sql = "SELECT SUM($field) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($project, $from, $to, $categories, $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null, $sortColumn = 1, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array(
            "searchQuery" => "%" . $searchQuery . "%",
            "project" => $project,
            "from" => $from,
            "to" => $to
        );

        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($planned)) {
            $bindings["planned"] = $planned;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        $sort = "id";
        switch ($sortColumn) {
            case 1:
                $sort = "IFNULL(DATE(t.start),DATE(t.end))";
                break;
            case 2:
                $sort = "TIME(t.start)";
                break;
            case 3:
                $sort = "TIME(t.end)";
                break;
            case 4:
                $sort = "t.duration";
                break;
            case 5:
                $sort = "customerName";
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
            . "GROUP_CONCAT(tc.name SEPARATOR ', ') as categories, "
            . "t.is_billed, "
            . "t.is_payed, "
            . "t.is_planned, "
            . "t.reference_sheet, "
            . "tcus.name as customerName, "
            . "tcus.id as customer";

        list($tableSQL, $cat_bindings) = $this->getTableSQL($select, $categories, $include_empty_categories, $billed, $payed, $planned, $customer);

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

    public function getCustomerNameFromSheet($sheet_id) {
        $sql = "SELECT c.name "
            . "FROM " . $this->getTableName("timesheets_sheets") . " s "
            . " LEFT JOIN " . $this->getTableName("timesheets_customers") . " c ON c.id = s.customer "
            . " WHERE s.id = :sheet";

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
            . " WHERE s.is_planned = 0 "
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

    public function getUsers($id, $only_id = false) {
        $sql = "SELECT u.id, u.login "
            . "FROM " . $this->getTableName("timesheets_projects_users") . " project_user,"
            . "" . $this->getTableName() . " sheet, "
            . "" . $this->getTableName("global_users") . " u "
            . "WHERE sheet.id = :id "
            . "AND sheet.project = project_user.project "
            . "AND project_user.user = u.id ";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            if ($only_id) {
                $results[] = intval($row["id"]);
            } else {
                $results[intval($row["id"])] = $row["login"];
            }
        }
        return $results;
    }

    public function getSheetIDsFromProject($id) {
        $sql = "SELECT id FROM " . $this->getTableName() . " WHERE project = :id ";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetchColumn()) {
            $results[] = $row;
        }
        return $results;
    }

    public function setSheetsPayedState($sheets = [], $is_payed = 0) {

        if (count($sheets) <= 0) {
            return;
        }

        $bindings = ["is_payed" => $is_payed];

        $sheet_bindings = array();
        foreach ($sheets as $idx => $sheet) {
            $sheet_bindings[":sheet_" . $idx] = $sheet;
        }

        $sql = "UPDATE " . $this->getTableName() . " SET is_payed = :is_payed WHERE id IN  (" . implode(',', array_keys($sheet_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_merge($bindings, $sheet_bindings));

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function setSheetsBilledState($sheets = [], $is_billed = 0) {

        if (count($sheets) <= 0) {
            return;
        }

        $bindings = ["is_billed" => $is_billed];

        $sheet_bindings = array();
        foreach ($sheets as $idx => $sheet) {
            $sheet_bindings[":sheet_" . $idx] = $sheet;
        }

        $sql = "UPDATE " . $this->getTableName() . " SET is_billed = :is_billed WHERE id IN  (" . implode(',', array_keys($sheet_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_merge($bindings, $sheet_bindings));

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function setSheetsPlannedState($sheets = [], $is_planned = 0) {

        if (count($sheets) <= 0) {
            return;
        }

        $bindings = ["is_planned" => $is_planned];

        $sheet_bindings = array();
        foreach ($sheets as $idx => $sheet) {
            $sheet_bindings[":sheet_" . $idx] = $sheet;
        }

        $sql = "UPDATE " . $this->getTableName() . " SET is_planned = :is_planned WHERE id IN  (" . implode(',', array_keys($sheet_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_merge($bindings, $sheet_bindings));

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function getSeriesSheets($project, $sheet) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  "
            . "WHERE project = :project "
            . " AND ( "
            . " reference_sheet = (SELECT reference_sheet FROM timesheets_sheets WHERE ID = :sheet)"
            . " OR reference_sheet = :sheet "
            . " OR id = (SELECT reference_sheet FROM timesheets_sheets WHERE ID = :sheet)"
            . " OR (id = :sheet AND "
            . "  EXISTS ( "
            . "   SELECT 1 "
            . "   FROM timesheets_sheets "
            . "   WHERE reference_sheet = :sheet "
            . " ) "
            . ")"
            . ")";

        $bindings = array("project" => $project, "sheet" => $sheet);

        $sql .= " ORDER BY start, id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function updateReferenceSheet($old_sheet, $sheets) {

        $error = false;
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE " . $this->getTableName() . " 
                    SET reference_sheet = :new_sheet 
                    WHERE reference_sheet = :old_sheet 
                    AND reference_sheet != :new_sheet 
                    AND id != :old_sheet";
            $bindings = array("new_sheet" => $sheets[0], "old_sheet" => $old_sheet);
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($bindings);

            if (!$result) {
                throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
            }

            $sql2 = "UPDATE " . $this->getTableName() . " SET reference_sheet = null WHERE id = :sheet_id";
            $bindings2 = array("sheet_id" => $sheets[0]);
            $stmt2 = $this->db->prepare($sql2);
            $result2 = $stmt2->execute($bindings2);

            if (!$result2) {
                throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
            }

            $updated1 = $stmt->rowCount();
            $updated2 = $stmt2->rowCount();

            $this->db->commit();

            if ($updated1 == count($sheets) && $updated2 == 1) {
                return true;
            }

            $error = true;
        } catch (\Exception $e) {
            $error = true;
        }
        if ($error && $this->db->inTransaction()) {
            $this->db->rollBack();
        }

        return false;
    }

    public function getSheetForWidget($project_id, $start, $end) {
        $sql = "SELECT t.*, tcus.name as customerName "
            . " FROM " . $this->getTableName() . " t "
            . " LEFT JOIN " . $this->getTableName("timesheets_customers") . " tcus ON tcus.id = t.customer "
            . " WHERE t.project = :project_id"
            . " AND ("
            . "     (t.start >= :start AND t.end <= :end ) OR"
            . "     (t.start >= :start AND t.start <= :end AND t.end IS NULL ) OR"
            . "     (t.end >= :start AND t.end <= :end AND t.start IS NULL )"
            . " )"
            . " ORDER BY t.start";

        $bindings = [
            "project_id" => $project_id,
            "start" => $start,
            "end" => $end
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getOverview($project, $from, $to, $categories, $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null) {

        $bindings = [
            "searchQuery" => "%",
            "project" => $project,
            "from" => $from,
            "to" => $to
        ];

        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($planned)) {
            $bindings["planned"] = $planned;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        $select = "COUNT(t.id) as count, tcus.name as customerName, tcus.id as customer";
        list($tableSQL, $cat_bindings) = $this->getTableSQL($select, $categories, $include_empty_categories, $billed, $payed, $planned, $customer, 'tcus.id');

        $sql = $tableSQL;
        $sql .= " ORDER BY customerName";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }

    public function hasEqualSheet(Sheet $sheet) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  "
            . " WHERE "
            . " (project = :project OR (project IS NULL AND :project IS NULL)) AND "
            . " (start = :start OR (start IS NULL AND :start IS NULL)) AND "
            . " (end = :end OR (end IS NULL AND :end IS NULL)) AND "
            . " (duration = :duration OR (duration IS NULL AND :duration IS NULL)) AND "
            . " (duration_modified = :duration_modified OR (duration_modified IS NULL AND :duration_modified IS NULL)) AND "
            . " (start_lat = :start_lat OR (start_lat IS NULL AND :start_lat IS NULL)) AND "
            . " (start_lng = :start_lng OR (start_lng IS NULL AND :start_lng IS NULL)) AND "
            . " (start_acc = :start_acc OR (start_acc IS NULL AND :start_acc IS NULL)) AND "
            . " (end_lat = :end_lat OR (end_lat IS NULL AND :end_lat IS NULL)) AND "
            . " (end_lng = :end_lng OR (end_lng IS NULL AND :end_lng IS NULL)) AND "
            . " (end_acc = :end_acc OR (end_acc IS NULL AND :end_acc IS NULL)) AND "
            . " (is_billed = :is_billed OR (is_billed IS NULL AND :is_billed IS NULL)) AND "
            . " (is_payed = :is_payed OR (is_payed IS NULL AND :is_payed IS NULL)) AND "
            . " (is_planned = :is_planned OR (is_planned IS NULL AND :is_planned IS NULL)) AND "
            . " (reference_sheet = :reference_sheet OR (reference_sheet IS NULL AND :reference_sheet IS NULL)) AND"
            . " (customer = :customer OR (customer IS NULL AND :customer IS NULL))"
            . " ";

        $bindings = [
            "project" => $sheet->project,
            "start" => $sheet->start,
            "end" => $sheet->end,
            "duration" => $sheet->duration,
            "duration_modified" => $sheet->duration_modified,
            "start_lat" => $sheet->start_lat,
            "start_lng" => $sheet->start_lng,
            "start_acc" => $sheet->start_acc,
            "end_lat" => $sheet->end_lat,
            "end_lng" => $sheet->end_lng,
            "end_acc" => $sheet->end_acc,
            "is_billed" => $sheet->is_billed,
            "is_payed" => $sheet->is_payed,
            "is_planned" => $sheet->is_planned,
            "reference_sheet" => $sheet->reference_sheet,
            "customer" => $sheet->customer
        ];

        var_dump($sheet->reference_sheet);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            var_dump($row);
        }

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
