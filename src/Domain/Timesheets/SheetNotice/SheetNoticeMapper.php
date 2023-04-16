<?php

namespace App\Domain\Timesheets\SheetNotice;

use App\Domain\DataObject;

class SheetNoticeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_notices";
    protected $dataobject = \App\Domain\Timesheets\SheetNotice\SheetNotice::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function __construct(\PDO $db, \App\Domain\Main\Translator $translation, \App\Domain\Base\CurrentUser $user) {
        parent::__construct($db, $translation, $user);
    }

    public function getNotice($sheet_id) {
        $sql = "SELECT *, notice FROM " . $this->getTableName() . "  WHERE sheet = :sheet AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'sheet' => $sheet_id
        ]);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function hasNotice($sheet_id) {
        $sql = "SELECT id FROM " . $this->getTableName() . "  WHERE sheet = :sheet AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'sheet' => $sheet_id
        ]);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

    public function hasNotices($sheet_ids = []) {
        if (empty($sheet_ids)) {
            return [];
        }
        $sql = "SELECT sheet FROM " . $this->getTableName();

        $notice_bindings = [];
        foreach ($sheet_ids as $idx => $sheet_id) {
            $notice_bindings[":sheet_" . $idx] = $sheet_id;
        }

        $sql .= " WHERE sheet IN (" . implode(',', array_keys($notice_bindings)) . ") AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($notice_bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row["sheet"];
        }
        return $results;
    }

    public function insert(DataObject $data) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE " . $this->getTableName() . " SET is_active = 0 WHERE sheet = :sheet ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["sheet" => $data->sheet]);

            $inserted_id = parent::insert($data);

            $this->db->commit();

            return $inserted_id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
        }

        return null;
    }
}
