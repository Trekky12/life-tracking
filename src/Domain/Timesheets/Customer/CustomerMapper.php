<?php

namespace App\Domain\Timesheets\Customer;

class CustomerMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_customers";
    protected $dataobject = \App\Domain\Timesheets\Customer\Customer::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false; // do not use "user" field but "createdBy"

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
