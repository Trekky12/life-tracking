<?php

namespace App\Domain\Timesheets\SheetFile;

class SheetFileMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_files";
    protected $dataobject = \App\Domain\Timesheets\SheetFile\SheetFile::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getFiles($sheet) {
        $sql = "SELECT id, name, type, filename, encryptedCEK FROM " . $this->getTableName($this->table) . " WHERE sheet = :sheet";

        $bindings = ["sheet" => $sheet];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }

    public function hasFiles($sheet_ids = []) {
        if (empty($sheet_ids)) {
            return [];
        }
        $sql = "SELECT sheet, COUNT(*) as count FROM " . $this->getTableName($this->table);

        $bindings = [];
        foreach ($sheet_ids as $idx => $sheet_id) {
            $bindings[":sheet_" . $idx] = $sheet_id;
        }

        $sql .= " WHERE sheet IN (" . implode(',', array_keys($bindings)) . ") GROUP BY sheet";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["sheet"]] = $row["count"];
        }
        return $results;
    }
}
