<?php

namespace App\Domain\Timesheets\ProjectNotice;

use App\Domain\DataObject;

class ProjectNoticeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_notices";
    protected $dataobject = \App\Domain\Timesheets\ProjectNotice\ProjectNotice::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getNotice($project_id) {
        $sql = "SELECT *, notice FROM " . $this->getTableName() . "  WHERE project = :project AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'project' => $project_id
        ]);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function hasNotice($project_id) {
        $sql = "SELECT id FROM " . $this->getTableName() . "  WHERE project = :project AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'project' => $project_id
        ]);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

    public function hasNotices($project_ids = []) {
        if (empty($project_ids)) {
            return [];
        }
        $sql = "SELECT project FROM " . $this->getTableName();

        $notice_bindings = [];
        foreach ($project_ids as $idx => $project_id) {
            $notice_bindings[":project" . $idx] = $project_id;
        }

        $sql .= " WHERE project IN (" . implode(',', array_keys($notice_bindings)) . ") AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($notice_bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row["project"];
        }
        return $results;
    }

    public function insert(DataObject $data) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE " . $this->getTableName() . " SET is_active = 0 WHERE project = :project ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["project" => $data->project]);

            $inserted_id = parent::insert($data);

            $this->db->commit();

            return $inserted_id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
        }

        return null;
    }
}
