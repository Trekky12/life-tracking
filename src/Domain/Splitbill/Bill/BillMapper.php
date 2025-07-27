<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\Splitbill\BaseBillMapper;

class BillMapper extends BaseBillMapper {

    protected $table = "splitbill_bill";
    protected $dataobject = \App\Domain\Splitbill\Bill\Bill::class;
    protected $bill_balance_table = "splitbill_bill_users";

    public function getBalances() {
        $sql = "SELECT b.sbgroup, SUM(bb.paid) as paid, SUM(bb.spend) as spend, SUM(bb.paid-bb.spend) as balance FROM " . $this->getTableName() . " b "
            . " LEFT JOIN " . $this->getTableName($this->bill_balance_table) . " bb "
            . " ON b.id = bb.bill "
            . " WHERE bb.user = :user "
            . " GROUP BY b.sbgroup";

        $bindings = array("user" => $this->user_id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[intval($row["sbgroup"])] = [
                "spend" => floatval($row["spend"]),
                "paid" => floatval($row["paid"]),
                "balance" => floatval($row["balance"])
            ];
        }
        return $results;
    }

    public function getBillUsers($id) {
        $sql = "SELECT user FROM " . $this->getTableName($this->bill_balance_table) . " WHERE (spend > 0 OR paid > 0) AND bill = :id ";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    /**
     * Table
     */
    private function getTableSQL($select) {
        $sql = "SELECT {$select} FROM " . $this->getTableName() . " b "
            . " LEFT JOIN " . $this->getTableName($this->bill_balance_table) . " bb "
            . " ON b.id = bb.bill AND bb.user = :user "
            . " WHERE b.sbgroup = :group "
            . " AND "
            . " ( b.name LIKE :searchQuery OR "
            . " bb.paid LIKE :searchQuery OR "
            . " bb.spend LIKE :searchQuery )";
        return $sql;
    }

    public function tableCount($group, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "user" => $this->user_id, "group" => $group);

        $sql = $this->getTableSQL("COUNT(b.id)");

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($group, $group_users, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array("searchQuery" => "%" . $searchQuery . "%", "user" => $this->user_id, "group" => $group);

        $sort = "id";
        switch ($sortColumn) {
            case 0:
                $sort = "date";
                break;
            case 1:
                $sort = "time";
                break;
            case 2:
                $sort = "name";
                break;
            case 3:
                $sort = "bb.spend";
                break;
            case 4:
                $sort = "bb.paid";
                break;
            case 5:
                $sort = "balance";
                break;
        }
        if ($group_users < 2) {
            switch ($sortColumn) {
                case 3:
                    $sort = "bb.paid";
                    break;
            }
        }

        $select = "b.*, bb.spend, bb.paid, bb.paid-bb.spend as balance";
        $sql = $this->getTableSQL($select);

        $sql .= " ORDER BY {$sort} {$sortDirection}, b.time {$sortDirection}, b.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
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
