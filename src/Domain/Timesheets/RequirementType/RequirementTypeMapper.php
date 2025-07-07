<?php

namespace App\Domain\Timesheets\RequirementType;

class RequirementTypeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_requirement_types";
    protected $dataobject = \App\Domain\Timesheets\RequirementType\RequirementType::class;
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
}
