<?php

namespace App\Domain\Timesheets\CustomerRequirement;

class CustomerRequirementMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_customers_requirements";
    protected $dataobject = \App\Domain\Timesheets\CustomerRequirement\CustomerRequirement::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getFromType($id, $valid = null, $order = 'id ASC') {

        $valid_query = "(CURRENT_DATE BETWEEN start AND end) AND (DATE(createdOn) BETWEEN start AND end)";

        $sql = "SELECT *, CASE
                    WHEN " . $valid_query . " THEN 1
                    ELSE 0
                END AS is_valid 
                FROM " . $this->getTableName() . " WHERE requirement_type = :id ";

        $bindings = array("id" => $id);

        if (!is_null($valid) && strlen($valid) > 0) {

            if ($valid) {
                $sql .= " AND " . $valid_query;
            } else {
                $sql .= " AND NOT " . $valid_query;
            }
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

    public function getFromCustomer($id, $order = 'id DESC') {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE customer = :id ";

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

    public function getCustomersState($project_id, $requirement_type_id = null) {
        $sql = "SELECT c.id AS customer_id, c.name AS customer_name, rt.id as requirement_type_id, ";
        $sql .= "rt.name AS requirement_type_name," .
            "MAX( " .
            "    CASE " .
            "    WHEN cr.id IS NOT NULL " .
            "        AND CURRENT_DATE BETWEEN cr.start AND cr.end " .
            "        AND DATE(cr.createdOn) BETWEEN cr.start AND cr.end " .
            "    THEN 1 " .
            "    ELSE 0 " .
            "    END " .
            ") AS is_valid ";

        $sql .= "FROM " . $this->getTableName("timesheets_customers") . " c ";
        $sql .= "JOIN " . $this->getTableName("timesheets_requirement_types") . " rt ";
        if (!is_null($requirement_type_id)) {
            $sql .= "ON rt.id = :requirement_type_id ";
        } else {
            $sql .= "ON rt.project = c.project ";
        }

        $sql .= "LEFT JOIN " . $this->getTableName() . " cr ";
        $sql .= "ON cr.customer = c.id AND cr.requirement_type = rt.id ";
        $sql .= "WHERE c.project = :project ";
        $sql .= "AND c.archive = 0 ";
        $sql .= "GROUP BY c.id, c.name, rt.id, rt.name ";
        $sql .= "ORDER BY c.name ASC, rt.name ";

        $bindings = ["project" => $project_id];

        if (!is_null($requirement_type_id)) {
            $bindings["requirement_type_id"] = $requirement_type_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
