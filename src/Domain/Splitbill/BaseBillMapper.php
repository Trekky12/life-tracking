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

}
