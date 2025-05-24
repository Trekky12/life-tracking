<?php

namespace App\Domain\Timesheets\NoticeField;

class NoticeFieldMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_noticefields";
    protected $dataobject = \App\Domain\Timesheets\NoticeField\NoticeField::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getFromProject($id, $type = null, $order = 'position ASC') {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE project = :id ";

        $bindings = array("id" => $id);

        if (!is_null($type)) {
            $sql .= " AND type = :type";
            $bindings["type"] = $type;
        }

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

    public function set_default($project_id, $type = 'sheet') {
        $sql = "UPDATE " . $this->getTableName() . " SET is_default = :is_default WHERE project = :project_id AND type = :type";
        $bindings = array("project_id" => $project_id, "is_default" => 1, "type" => $type);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function unset_default($project_id, $id, $type = 'sheet') {
        $sql = "UPDATE " . $this->getTableName() . " SET is_default = :is_default WHERE project = :project_id AND type = :type  AND id != :id";
        $bindings = array("project_id" => $project_id, "is_default" => 0, "id" => $id, "type" => $type);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function get_default($project_id, $type = 'sheet') {
        $sql = "SELECT id FROM " . $this->getTableName() . " WHERE project = :project_id AND is_default = :is_default AND type = :type";

        $bindings = array("project_id" => $project_id, "is_default" => 1, "type" => $type);
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }
}
