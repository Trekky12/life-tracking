<?php

namespace App\Domain\Timesheets\ProjectCategory;

class ProjectCategoryMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_categories";
    protected $dataobject = \App\Domain\Timesheets\ProjectCategory\ProjectCategory::class;
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

}
