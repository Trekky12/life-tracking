<?php

namespace App\Domain\Splitbill;

class BaseBillMapper extends \App\Domain\Mapper {

    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $bill_balance_table = "";

    public function addOrUpdateBalance($bill, $user, $paid, $spend, $paymethod_spend = null, $paymethod_paid = null, $paid_foreign = null, $spend_foreign = null) {

        $bindings = ["user" => $user, "bill" => $bill];

        $sql = "SELECT id FROM " . $this->getTableName($this->bill_balance_table) . "  WHERE bill = :bill AND user =:user ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        // no entry present, so create one
        if ($stmt->rowCount() > 0) {
            return $this->updateBalance($bill, $user, $paid, $spend, $paymethod_spend, $paymethod_paid, $paid_foreign, $spend_foreign);
        } else {
            return $this->addBalance($bill, $user, $paid, $spend, $paymethod_spend, $paymethod_paid, $paid_foreign, $spend_foreign);
        }
    }

    private function addBalance($bill, $user, $paid, $spend, $paymethod_spend = null, $paymethod_paid = null, $paid_foreign = null, $spend_foreign = null) {
        $bindings = ["user" => $user, "bill" => $bill, "paid" => $paid, "spend" => $spend, "paymethod_spend" => $paymethod_spend, "paymethod_paid" => $paymethod_paid, "paid_foreign" => $paid_foreign, "spend_foreign" => $spend_foreign];

        $sql = "INSERT INTO " . $this->getTableName($this->bill_balance_table) . " (user, paid, spend, bill, paymethod_spend, paymethod_paid, paid_foreign, spend_foreign) VALUES (:user, :paid, :spend, :bill, :paymethod_spend, :paymethod_paid, :paid_foreign, :spend_foreign)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return true;
    }

    private function updateBalance($bill, $user, $paid, $spend, $paymethod_spend = null, $paymethod_paid = null, $paid_foreign = null, $spend_foreign = null) {
        $bindings = ["user" => $user, "bill" => $bill, "paid" => $paid, "spend" => $spend, "paymethod_spend" => $paymethod_spend, "paymethod_paid" => $paymethod_paid, "paid_foreign" => $paid_foreign, "spend_foreign" => $spend_foreign];

        $sql = "UPDATE " . $this->getTableName($this->bill_balance_table) . " SET paid = :paid, spend = :spend, paymethod_spend = :paymethod_spend, paymethod_paid = :paymethod_paid, paid_foreign = :paid_foreign, spend_foreign = :spend_foreign WHERE user = :user AND bill = :bill";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function deleteBalanceofUser($bill, $user) {
        $sql = "DELETE FROM " . $this->getTableName($this->bill_balance_table) . "  WHERE bill = :bill AND user =:user ";
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

    public function getBalance($id) {
        $sql = "SELECT user, spend, paid, paid-spend as balance, paymethod_spend, paymethod_paid, paid_foreign, spend_foreign FROM " . $this->getTableName($this->bill_balance_table) . " WHERE bill = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["user"]] = $row;
        }
        return $results;
    }

    public function getBillSpend($id, $field = "spend") {
        $sql = "SELECT SUM({$field}) FROM " . $this->getTableName($this->bill_balance_table) . " WHERE bill = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return floatval($stmt->fetchColumn());
        }
        return 0;
    }

    public function updatePaymethod($bill, $user, $paymethod_spend = null, $paymethod_paid = null) {
        $bindings = ["user" => $user, "bill" => $bill, "paymethod_spend" => $paymethod_spend, "paymethod_paid" => $paymethod_paid, ];

        $sql = "UPDATE " . $this->getTableName($this->bill_balance_table) . " SET paymethod_spend = :paymethod_spend, paymethod_paid = :paymethod_paid WHERE user = :user AND bill = :bill";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_NOT_POSSIBLE'));
        }
        return true;
    }

    public function getTotalBalance($group) {
        $sql = "SELECT bb.user, SUM(bb.paid) as paid, SUM(bb.spend) as spend, SUM(bb.paid-bb.spend) as balance FROM " . $this->getTableName() . " b "
            . " LEFT JOIN " . $this->getTableName($this->bill_balance_table) . " bb "
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
        $sql = "SELECT bb.user, SUM(bb.spend) as spend FROM " . $this->getTableName() . " b "
            . " LEFT JOIN " . $this->getTableName($this->bill_balance_table) . " bb "
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

}
