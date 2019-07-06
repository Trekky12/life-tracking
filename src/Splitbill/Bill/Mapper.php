<?php

namespace App\Splitbill\Bill;

class Mapper extends \App\Base\Mapper {

    protected $table = "splitbill_bill";
    protected $model = "\App\Splitbill\Bill\Bill";
    protected $filterByUser = false;
    protected $insertUser = true;
    private $bill_balance_table = "splitbill_bill_users";

    public function addOrUpdateBalance($bill, $user, $paid, $spend) {

        $bindings = ["user" => $user, "bill" => $bill];

        $sql = "SELECT id FROM " . $this->getTable($this->bill_balance_table) . "  WHERE bill = :bill AND user =:user ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        // no entry present, so create one
        if ($stmt->rowCount() > 0) {
            return $this->updateBalance($bill, $user, $paid, $spend);
        } else {
            return $this->addBalance($bill, $user, $paid, $spend);
        }
    }

    private function addBalance($bill, $user, $paid, $spend) {
        $bindings = ["user" => $user, "bill" => $bill, "paid" => $paid, "spend" => $spend];

        $sql = "INSERT INTO " . $this->getTable($this->bill_balance_table) . " (user, paid, spend, bill) VALUES (:user, :paid, :spend, :bill)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    private function updateBalance($bill, $user, $paid, $spend) {
        $bindings = ["user" => $user, "bill" => $bill, "paid" => $paid, "spend" => $spend];

        $sql = "UPDATE " . $this->getTable($this->bill_balance_table) . " SET paid = :paid, spend = :spend WHERE user = :user AND bill = :bill";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function deleteBalanceofUser($bill, $user) {
        $sql = "DELETE FROM " . $this->getTable($this->bill_balance_table) . "  WHERE bill = :bill AND user =:user ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "bill" => $bill,
            "user" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getBalance($id) {
        $sql = "SELECT user, spend, paid, paid-spend as balance FROM " . $this->getTable($this->bill_balance_table) . " WHERE bill = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["user"]] = $row;
        }
        return $results;
    }

    public function getBillSpend($id) {
        $sql = "SELECT SUM(spend) FROM " . $this->getTable($this->bill_balance_table) . " WHERE bill = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return floatval($stmt->fetchColumn());
        }
        return 0;
    }

    public function getTotalBalance($group) {
        $sql = "SELECT bb.user, SUM(bb.paid) as paid, SUM(bb.spend) as spend, SUM(bb.paid-bb.spend) as balance FROM " . $this->getTable() . " b "
                . " LEFT JOIN " . $this->getTable($this->bill_balance_table) . " bb "
                . " ON b.id = bb.bill "
                . " WHERE b.sbgroup = :group "
                . " GROUP BY bb.user"
                . " ORDER by balance, paid DESC, spend DESC";

        $bindings = array("group" => $group);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[intval($row["user"])] = [
                "user" => intval($row["user"]),
                "spend" => floatval($row["spend"]),
                "paid" => floatval($row["paid"]),
                "balance" => floatval($row["balance"]),
                "owe" => 0
            ];
        }
        return $results;
    }
    
    public function getSettledUpSpendings($group, $settleup = 1) {
        $sql = "SELECT bb.user, SUM(bb.spend) as spend FROM " . $this->getTable() . " b "
                . " LEFT JOIN " . $this->getTable($this->bill_balance_table) . " bb "
                . " ON b.id = bb.bill "
                . " WHERE b.sbgroup = :group AND b.settleup = :settleup "
                . " GROUP BY bb.user";

        $bindings = array("group" => $group, "settleup" => $settleup);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[intval($row["user"])] = floatval($row["spend"]);
        }
        return $results;
    }
    
    public function getBalances() {
        $sql = "SELECT b.sbgroup, SUM(bb.paid) as paid, SUM(bb.spend) as spend, SUM(bb.paid-bb.spend) as balance FROM " . $this->getTable() . " b "
                . " LEFT JOIN " . $this->getTable($this->bill_balance_table) . " bb "
                . " ON b.id = bb.bill "
                . " WHERE bb.user = :user "
                . " GROUP BY b.sbgroup";

        $bindings = array("user" => $this->userid);

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

    /**
     * Table
     */
    private function getTableSQL($select) {
        $sql = "SELECT {$select} FROM " . $this->getTable() . " b "
                . " LEFT JOIN " . $this->getTable($this->bill_balance_table) . " bb "
                . " ON b.id = bb.bill AND bb.user = :user "
                . " WHERE b.sbgroup = :group "
                . " AND "
                . " ( b.name LIKE :searchQuery OR "
                . " bb.paid LIKE :searchQuery OR "
                . " bb.spend LIKE :searchQuery )";
        return $sql;
    }

    public function tableCount($group, $searchQuery = "%") {

        $bindings = array("searchQuery" => $searchQuery, "user" => $this->userid, "group" => $group);

        $sql = $this->getTableSQL("COUNT(b.id)");

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_DATA'));
    }

    public function getTableData($group, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = array("searchQuery" => "%" . $searchQuery . "%", "user" => $this->userid, "group" => $group);

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
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

}
