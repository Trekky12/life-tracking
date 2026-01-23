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
    private function getTableSQL($select, $categories, $include_empty_categories = true, $invoiced = null, $billed = null, $payed = null, $happened = null, $customer = null, $group_by = 't.id', $date_modified = false) {
        $cat_bindings = array();
        if (!is_null($categories)) {
            foreach ($categories as $idx => $cat) {
                $cat_bindings[":cat_" . $idx] = $cat;
            }
        }

        $dateFilter = "(" . $this->filterDate("t.start", "t.end") . ") OR (" . $this->filterDate("t.start_modified", "t.end_modified") . ")";
        if ($date_modified) {
            $dateFilter = $this->filterDate("COALESCE(t.start_modified, t.start)", "COALESCE(t.end_modified, t.end)");
        }

        $sql = "SELECT {$select} FROM " . $this->getTableName() . " t"
            . " LEFT JOIN " . $this->getTableName("timesheets_customers") . " tcus ON tcus.id = t.customer "
            . " LEFT JOIN ( "
            . "     SELECT  "
            . "         tcs.sheet,"
            . "         GROUP_CONCAT(tc.name ORDER BY tc.name SEPARATOR ', ') AS categories "
            . "     FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs "
            . "     LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc ON tc.id = tcs.category "
            . "     GROUP BY tcs.sheet "
            . " ) AS c ON c.sheet = t.id "
            . " WHERE t.project = :project "
            . " AND (" . $dateFilter . ")"
            . " AND ("
            . "     t.start LIKE :searchQuery OR "
            . "     t.end LIKE :searchQuery OR "
            . "     t.start_modified LIKE :searchQuery OR "
            . "     t.end_modified LIKE :searchQuery OR "
            . "     tcus.name LIKE :searchQuery OR "
            . "     (t.id IN ( "
            . "              SELECT tcs2.sheet "
            . "                 FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs2 "
            . "                 LEFT JOIN " . $this->getTableName("timesheets_categories") . " tc2 ON tc2.id = tcs2.category "
            . "                 WHERE tc2.name LIKE :searchQuery "
            . "             ) "
            . "     ) "
            . ")";

        if (!empty($cat_bindings)) {
            $sql .= " AND ("
                . " EXISTS ("
                . "     SELECT 1"
                . "     FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs3"
                . "     WHERE tcs3.sheet = t.id"
                . "       AND tcs3.category IN (" . implode(',', array_keys($cat_bindings)) . ")"
                . "     GROUP BY tcs3.sheet"
                . "     HAVING COUNT(DISTINCT tcs3.category) >= " . count($cat_bindings)
                . " )";

            if ($include_empty_categories) {
                $sql .= " OR NOT EXISTS ("
                    . "     SELECT 1"
                    . "     FROM " . $this->getTableName("timesheets_sheets_categories") . " tcs4"
                    . "     WHERE tcs4.sheet = t.id"
                    . " )";
            }

            $sql .= ")";
        }

        if (!is_null($invoiced)) {
            $sql .= " AND t.is_invoiced = :invoiced ";
        }

        if (!is_null($billed)) {
            $sql .= " AND t.is_billed = :billed ";
        }

        if (!is_null($payed)) {
            $sql .= " AND t.is_payed = :payed ";
        }

        if (!is_null($happened)) {
            $sql .= " AND t.is_happened = :happened ";
        }

        if (!is_null($customer)) {
            $sql .= " AND t.customer = :customer ";
        }

        $sql .= " GROUP BY {$group_by}";
        return [$sql, $cat_bindings];
    }

    public function tableCount($project, $from, $to, $categories, $include_empty_categories = true, $invoiced = null, $billed = null, $payed = null, $happened = null, $customer = null, $searchQuery = "%") {

        $bindings = array(
            "searchQuery" => $searchQuery,
            "project" => $project,
            "from" => $from,
            "to" => $to
        );

        if (!is_null($invoiced)) {
            $bindings["invoiced"] = $invoiced;
        }
        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($happened)) {
            $bindings["happened"] = $happened;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        list($tableSQL, $cat_bindings) = $this->getTableSQL("t.id", $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer);

        $sql = "SELECT COUNT(t.id) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function tableSum($project, $from, $to, $categories, $include_empty_categories = true, $invoiced = null, $billed = null, $payed = null, $happened = null, $customer = null, $searchQuery = "%", $field = "t.duration") {

        $bindings = array(
            "searchQuery" => $searchQuery,
            "project" => $project,
            "from" => $from,
            "to" => $to
        );
        if (!is_null($invoiced)) {
            $bindings["invoiced"] = $invoiced;
        }
        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($happened)) {
            $bindings["happened"] = $happened;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        list($tableSQL, $cat_bindings) = $this->getTableSQL($field, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer);

        $sql = "SELECT SUM($field) FROM ";
        $sql .= "(" . $tableSQL . ") as t";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(array_merge($bindings, $cat_bindings));
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($project, $from, $to, $categories, $include_empty_categories = true, $invoiced = null, $billed = null, $payed = null, $happened = null, $customer = null, $sortColumn = 1, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array(
            "searchQuery" => "%" . $searchQuery . "%",
            "project" => $project,
            "from" => $from,
            "to" => $to
        );

        if (!is_null($invoiced)) {
            $bindings["invoiced"] = $invoiced;
        }
        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($happened)) {
            $bindings["happened"] = $happened;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        $sort = "id";
        switch ($sortColumn) {
            case 'date':
                $sort = "IFNULL(DATE(t.start),DATE(t.end))";
                break;
            case 'start':
                $sort = "TIME(t.start)";
                break;
            case 'end':
                $sort = "TIME(t.end)";
                break;
            case 'difference':
                $sort = "t.duration";
                break;
            case 'customer':
                $sort = "customerName";
                break;
            case 'is_happened':
                $sort = "t.is_happened";
                break;
            case 'is_invoiced':
                $sort = "t.is_invoiced";
                break;
            case 'is_billed':
                $sort = "t.is_billed";
                break;
            case 'is_payed':
                $sort = "t.is_payed";
                break;
        }

        $select = "t.id, "
            . "t.createdBy, "
            . "t.createdOn, "
            . "t.changedBy, "
            . "t.changedOn, "
            . "t.project, "
            . "t.start, "
            . "t.end, "
            . "t.duration, "
            . "t.duration_modified, "
            . "t.start_modified, "
            . "t.end_modified, "
            . "c.categories, "
            . "t.is_invoiced, "
            . "t.is_billed, "
            . "t.is_payed, "
            . "t.is_happened, "
            . "t.reference_sheet, "
            . "tcus.name as customerName, "
            . "tcus.id as customer";

        list($tableSQL, $cat_bindings) = $this->getTableSQL($select, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer);

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
            . " WHERE s.is_happened = 1 "
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

    public function setSheetsInvoicedState($sheets = [], $is_invoiced = 0) {

        if (count($sheets) <= 0) {
            return;
        }

        $bindings = ["is_invoiced" => $is_invoiced];

        $sheet_bindings = array();
        foreach ($sheets as $idx => $sheet) {
            $sheet_bindings[":sheet_" . $idx] = $sheet;
        }

        $sql = "UPDATE " . $this->getTableName() . " SET is_invoiced = :is_invoiced WHERE id IN  (" . implode(',', array_keys($sheet_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_merge($bindings, $sheet_bindings));

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function setSheetsHappenedState($sheets = [], $happened = 0) {

        if (count($sheets) <= 0) {
            return;
        }

        $bindings = ["is_happened" => $happened];

        $sheet_bindings = array();
        foreach ($sheets as $idx => $sheet) {
            $sheet_bindings[":sheet_" . $idx] = $sheet;
        }

        $sql = "UPDATE " . $this->getTableName() . " SET is_happened = :is_happened WHERE id IN  (" . implode(',', array_keys($sheet_bindings)) . ")";

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
            . "     (t.start <= :end) AND "
            . "     (t.end >= :start OR t.end IS NULL) "
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

    public function getOverview($project, $from, $to, $categories, $include_empty_categories = true, $invoiced = null, $billed = null, $payed = null, $happened = null, $customer = null, $dateformat = "YYYY-MM-DD", $date_modified = true) {

        $bindings = [
            "searchQuery" => "%",
            "project" => $project,
            "from" => $from,
            "to" => $to
        ];

        if (!is_null($invoiced)) {
            $bindings["invoiced"] = $invoiced;
        }
        if (!is_null($billed)) {
            $bindings["billed"] = $billed;
        }
        if (!is_null($payed)) {
            $bindings["payed"] = $payed;
        }
        if (!is_null($happened)) {
            $bindings["happened"] = $happened;
        }
        if (!is_null($customer)) {
            $bindings["customer"] = $customer;
        }

        $start = "t.start";
        if ($date_modified) {
            $start = "COALESCE(t.start_modified, t.start)";
        }

        $select = "COUNT(t.id) as count, tcus.name as customerName, tcus.id as customer, GROUP_CONCAT(DATE_FORMAT(DATE({$start}), '{$dateformat}') ORDER BY DATE({$start}) SEPARATOR ', ') AS dates";
        list($tableSQL, $cat_bindings) = $this->getTableSQL($select, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, 'tcus.id', $date_modified);

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
            . " (is_invoiced = :is_invoiced OR (is_invoiced IS NULL AND :is_invoiced IS NULL)) AND "
            . " (is_billed = :is_billed OR (is_billed IS NULL AND :is_billed IS NULL)) AND "
            . " (is_payed = :is_payed OR (is_payed IS NULL AND :is_payed IS NULL)) AND "
            . " (is_happened = :is_happened OR (is_happened IS NULL AND :is_happened IS NULL)) AND "
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
            "is_invoiced" => $sheet->is_invoiced,
            "is_billed" => $sheet->is_billed,
            "is_payed" => $sheet->is_payed,
            "is_happened" => $sheet->is_happened,
            "reference_sheet" => $sheet->reference_sheet,
            "customer" => $sheet->customer
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getSheetsFromIDs($project_id, $sheet_ids = []) {
        if (empty($sheet_ids)) {
            return [];
        }
        $sql = "SELECT * FROM " . $this->getTableName();

        $bindings = [
            "project" => $project_id
        ];
        foreach ($sheet_ids as $idx => $sheet_id) {
            $bindings[":sheet" . $idx] = $sheet_id;
        }

        $sql .= " WHERE project = :project AND id IN (" . implode(',', array_keys($bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function set_start_end_modified($id, $start_modified, $end_modified) {
        $sql = "UPDATE " . $this->getTableName() . " SET start_modified = :start_modified, end_modified = :end_modified WHERE id  = :id";
        $bindings = [
            "id" => $id,
            "start_modified" => $start_modified,
            "end_modified" => $end_modified,
        ];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function isLastSheetOfTheDayOverSince1hour($project) {
        $sql = "SELECT MAX(end) FROM " . $this->getTableName() . "  "
            . "WHERE project = :project "
            . " AND DATE(end) = CURDATE() "
            . " HAVING DATE_ADD(MAX(end), INTERVAL 1 HOUR) < NOW()";

        $bindings = array("project" => $project);

        $sql .= " ORDER BY start DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function isLastSheetOfTheDayOver($project) {
        $sql = "SELECT MAX(end) FROM " . $this->getTableName() . "  "
            . "WHERE project = :project "
            . " AND DATE(end) = CURDATE() "
            . " HAVING MAX(end) < NOW()";

        $bindings = array("project" => $project);

        $sql .= " ORDER BY start DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getLastCompletedSheet(int $projectId): ?array {
        $sql = "SELECT id FROM " . $this->getTableName() . " 
                WHERE project = :project
                    AND DATE(end) = CURDATE()
                    AND end <= NOW()
                ORDER BY end DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['project' => $projectId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }


    private function filterDate($startColumn, $endColumn) {
        return "     (DATE({$startColumn}) >= :from AND DATE({$endColumn}) <= :to ) OR"
            . "     (DATE({$startColumn}) >= :from AND DATE({$startColumn}) <= :to AND {$endColumn} IS NULL ) OR"
            . "     (DATE({$endColumn}) >= :from AND DATE({$endColumn}) <= :to AND {$startColumn} IS NULL )";
    }

    public function getMarkers($user_projects, $from, $to, $minLat, $maxLat, $minLng, $maxLng) {

        if (empty($user_projects)) {
            return [];
        }

        $hasBounds = !is_null($minLat) && !is_null($maxLat) && !is_null($minLng) && !is_null($maxLng);
        if ($hasBounds) {
            $query = "((start_lat BETWEEN :minLat AND :maxLat AND start_lng BETWEEN :minLng AND :maxLng) OR (end_lat BETWEEN :minLat AND :maxLat AND end_lng BETWEEN :minLng AND :maxLng))";
            $bindings = [
                "minLat" => $minLat,
                "maxLat" => $maxLat,
                "minLng" => $minLng,
                "maxLng" => $maxLng
            ];
        } else {
            $query = "((start_lat IS NOT NULL AND start_lng IS NOT NULL) OR (end_lat IS NOT NULL AND end_lng IS NOT NULL)) AND (" . $this->filterDate("t.start", "t.end"). ")";
            $bindings = [
                "from" => $from,
                "to" => $to
            ];
        }

        $project_bindings = [];
        foreach ($user_projects as $idx => $project) {
            $project_bindings[":project_" . $idx] = $project;
        }

        $sql = "SELECT * FROM " . $this->getTableName() . " as t 
                WHERE " . $query . "
                AND project IN (" . implode(',', array_keys($project_bindings)) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($bindings, $project_bindings));

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }
}
