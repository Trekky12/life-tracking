<?php

namespace App\Domain\Finances;

class FinancesMapper extends \App\Domain\Mapper {

    protected $table = 'finances';
    protected $dataobject = \App\Domain\Finances\FinancesEntry::class;

    private function getTableSQL($select) {
        $sql = "SELECT {$select} "
                . " FROM " . $this->getTableName() . " f LEFT JOIN " . $this->getTableName('finances_categories') . " fc "
                . " ON f.category = fc.id "
                . " WHERE (DATE(f.date) >= :from "
                . "   AND DATE(f.date) <= :to ) "
                . " AND "
                . " (f.date LIKE :searchQuery OR "
                . " f.time LIKE :searchQuery OR "
                . " f.type LIKE :searchQuery OR "
                . " fc.name LIKE :searchQuery OR "
                . " f.description LIKE :searchQuery OR "
                . " f.value LIKE :searchQuery )";
        return $sql;
    }

    public function tableCount($from, $to, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "from" => $from, "to" => $to);

        $sql = $this->getTableSQL("COUNT(f.id)");

        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($from, $to, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array("searchQuery" => "%" . $searchQuery . "%", "from" => $from, "to" => $to);

        $sort = "date";
        switch ($sortColumn) {
            case 0:
                $sort = "date";
                break;
            case 1:
                $sort = "time";
                break;
            case 2:
                $sort = "type";
                break;
            case 3:
                $sort = "category";
                break;
            case 4:
                $sort = "description";
                break;
            case 5:
                $sort = "value";
                break;
        }

        $select = "f.date, f.time, "
                . "f.type, "
                . "fc.name as category, f.description, f.value, f.id, f.bill";
        $sql = $this->getTableSQL($select);

        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $sql .= " ORDER BY {$sort} {$sortDirection}, f.time {$sortDirection}, f.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    public function tableSum($from, $to, $type = 0, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "type" => $type, "from" => $from, "to" => $to);

        $sql = $this->getTableSQL("SUM(f.value)");

        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $sql .= " AND type = :type";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function statsTotal() {
        $sql = "SELECT YEAR(date) as year, type,  SUM(value) as sum, COUNT(value) as count FROM " . $this->getTableName();

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " GROUP BY YEAR(date), type"
                . " ORDER BY YEAR(date) DESC";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsYear($year) {
        $sql = "SELECT YEAR(date) as year, MONTH(date) as month, type, SUM(value) as sum, COUNT(value) as count "
                . "FROM " . $this->getTableName() . " "
                . "WHERE YEAR(date) = :year ";

        $bindings = array("year" => $year);
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " GROUP BY YEAR(date), MONTH(date), type";
        $sql .= " ORDER BY YEAR(date) DESC, MONTH(date) DESC";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsCategory($year, $type) {
        $sql = "SELECT YEAR(date) as year, type, fc.name as category, fc.id as category_id, SUM(value) as sum, COUNT(value) as count "
                . "FROM " . $this->getTableName() . " f, " . $this->getTableName("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . " AND YEAR(date) = :year "
                . "AND f.type = :type ";

        $bindings = array("year" => $year, "type" => $type);
        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $sql .= " GROUP BY YEAR(date), type, category";
        $sql .= " ORDER BY YEAR(date) DESC, sum DESC, category ASC";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsCategoryDetail($year, $type, $category) {

        $sql = "SELECT id, date, time, type, description, value, bill FROM " . $this->getTableName() . " "
                . "WHERE category = :category "
                . "AND YEAR(date) = :year "
                . "AND type = :type ";

        $bindings = array("year" => $year, "type" => $type, "category" => $category);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMonthType($year, $month, $type) {

        $sql = "SELECT YEAR(date) as year, MONTH(date) as month, type, fc.name as category, SUM(value) as sum, COUNT(value) as count, f.category as category_id "
                . "FROM " . $this->getTableName() . " f, " . $this->getTableName("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . "AND MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type ";

        $bindings = array("year" => $year, "month" => $month, "type" => $type);
        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $sql .= " GROUP BY YEAR(date), MONTH(date), type, category";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMonthCategory($year, $month, $type, $category) {

        $sql = "SELECT id, date, time, type, description, value, bill FROM " . $this->getTableName() . " "
                . "WHERE category = :category "
                . "AND MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type ";

        $bindings = array("year" => $year, "month" => $month, "type" => $type, "category" => $category);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMailBalance($user, $month, $year, $type) {
        $sql = "SELECT SUM(value) FROM " . $this->getTableName() . " "
                . "WHERE MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type "
                . "AND user = :user "
                . "GROUP BY type";

        $bindings = array("year" => $year, "month" => $month, "type" => $type, "user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return floatval($stmt->fetchColumn());
    }

    public function statsMailExpenses($user, $month, $year, $limit = 5) {
        $sql = "SELECT f.date, f.description, fc.name as category, f.value "
                . "FROM " . $this->getTableName() . " f, " . $this->getTableName("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . "AND MONTH(f.date) = :month "
                . "AND YEAR(f.date) = :year "
                . "AND f.type = :type "
                . "AND f.user = :user ";

        $bindings = array("year" => $year, "month" => $month, "type" => 0, "user" => $user);
        $sql .= "ORDER BY value desc LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function set_category($id, $category) {
        $sql = "UPDATE " . $this->getTableName() . " SET category = :category WHERE id  = :id";
        $bindings = array("id" => $id, "category" => $category);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function statsBudget($budget) {

        $sql = "SELECT f.id, f.date, f.time, f.type, f.description, fc.name as category, f.value, f.bill FROM " . $this->getTableName() . " f,   " . $this->getTableName("finances_categories") . " fc,  " . $this->getTableName("finances_budgets_categories") . " fbc "
                . "WHERE f.category = fbc.category "
                . "AND fc.id = f.category "
                . "AND fbc.budget = :budget "
                . "AND MONTH(date) = MONTH(CURRENT_DATE()) "
                . "AND YEAR(date) = YEAR(CURRENT_DATE()) "
                . "AND f.type = :type ";

        $bindings = array("budget" => $budget, "type" => 0);
        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsBudgetRemains() {

        $sql = "SELECT f.id, f.date, f.time, f.type, f.description, fc.name as category, f.value, f.bill FROM " . $this->getTableName() . " f,   " . $this->getTableName("finances_categories") . " fc  "
                . "WHERE f.category NOT IN (SELECT category FROM " . $this->getTableName("finances_budgets_categories") . " ) "
                . "AND fc.id = f.category "
                . "AND MONTH(date) = MONTH(CURRENT_DATE()) "
                . "AND YEAR(date) = YEAR(CURRENT_DATE()) "
                . "AND f.type = :type ";

        $bindings = array("type" => 0);
        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function getMarkers($from, $to) {
        $bindings = ["from" => $from, "to" => $to];
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE date >= :from AND date <= :to AND lat IS NOT NULL AND lng IS NOT NULL ";

        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    /**
     * Bills
     */
    public function addOrUpdateFromBill(FinancesEntry $entry) {

        $bindings = ["user" => $entry->user, "bill" => $entry->bill];

        $sql = "SELECT id FROM " . $this->getTableName() . "  WHERE bill = :bill AND user =:user ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        // no entry present, so create one
        if ($stmt->rowCount() > 0) {
            return $this->updateFromBill($entry);
        } else {
            return $this->insert($entry);
        }
    }

    private function updateFromBill(FinancesEntry $entry) {
        $sql = "UPDATE " . $this->getTableName() . " "
                . " SET value = :value, "
                . "     common_value = :common_value, "
                . "     date = :date, "
                . "     time = :time, "
                . "     lat  = :lat,"
                . "     lng  = :lng, "
                . "     acc  = :acc, "
                . "     paymethod  = :paymethod, "
                . "     type  = :type, "
                //. "     description  = :description, "
                . "     common  = :common "
                . "WHERE bill = :bill AND user = :user";
        $bindings = [
            "bill" => $entry->bill,
            "user" => $entry->user,
            "value" => $entry->value,
            "common_value" => $entry->common_value,
            "date" => $entry->date,
            "time" => $entry->time,
            "lat" => $entry->lat,
            "lng" => $entry->lng,
            "acc" => $entry->acc,
            "paymethod" => $entry->paymethod,
            //"description" => $entry->description,
            "type" => $entry->type,
            "common" => $entry->common
        ];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return true;
    }

    public function deleteEntrywithBill($bill, $user) {
        $sql = "DELETE FROM " . $this->getTableName() . "  WHERE bill = :bill AND user =:user ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "bill" => $bill,
            "user" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function statsLastExpenses($limit = 5) {
        $sql = "SELECT f.date, f.time, f.description, fc.name as category, f.value "
                . "FROM " . $this->getTableName() . " f, " . $this->getTableName("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . "AND f.type = :type ";

        $bindings = array("type" => 0);
        $this->addSelectFilterForUser($sql, $bindings, "f.");

        $sql .= "ORDER BY f.date desc, f.time desc LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}
