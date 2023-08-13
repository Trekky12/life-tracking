<?php

namespace App\Domain\Finances\Transaction;

class TransactionMapper extends \App\Domain\Mapper {

    protected $table = 'finances_transactions';
    protected $dataobject = \App\Domain\Finances\Transaction\Transaction::class;
    //protected $select_results_of_user_only = true;

    public function getTransactionsOfAccount($account_id) {
        $bindings = ["account" => $account_id];

        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE  ORDER BY changedOn DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }


    private function getTableSQL($select) {
        $sql = "SELECT {$select} "
            . " FROM " . $this->getTableName() . " t "
            . " LEFT JOIN " . $this->getTableName('finances_accounts') . " fa_from ON t.account_from = fa_from.id "
            . " LEFT JOIN " . $this->getTableName('finances_accounts') . " fa_to ON t.account_to = fa_to.id "
            . " WHERE (t.account_from = :account OR t.account_to = :account) "
            . " AND "
            . " (t.createdOn LIKE :searchQuery OR "
            . " t.description LIKE :searchQuery OR "
            . " t.value LIKE :searchQuery OR "
            . " fa_from.name LIKE :searchQuery OR "
            . " fa_to.name LIKE :searchQuery)";
        return $sql;
    }

    public function tableCount($account_id, $searchQuery = "%") {

        $bindings = [
            "account" => $account_id,
            "searchQuery" => $searchQuery
        ];

        $sql = $this->getTableSQL("COUNT(t.id)");

        $this->addSelectFilterForUser($sql, $bindings, "t.");

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getTableData($account_id, $sortColumn = 0, $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = [
            "account" => $account_id,
            "searchQuery" => "%" . $searchQuery . "%"
        ];

        $sort = "t.date";
        switch ($sortColumn) {
            case 0:
            case 1:
                $sort = "t.date";
                break;
            case 2:
                $sort = "t.time";
                break;
            case 3:
                $sort = "t.description";
                break;
            case 4:
                $sort = "t.value";
                break;
            case 5:
                $sort = "fa_from.name";
                break;
            case 6:
                $sort = "fa_to.name";
                break;
        }

        $select = "t.id, t.date, t.time, t.description, t.value, fa_from.name as acc_from, fa_to.name as acc_to, t.finance_entry, t.bill_entry, t.is_confirmed";
        $sql = $this->getTableSQL($select);

        $this->addSelectFilterForUser($sql, $bindings, "t.");

        $sql .= " ORDER BY {$sort} {$sortDirection}, t.date {$sortDirection}, t.time {$sortDirection}, t.id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Bills
     */
    public function getEntryFromBill($user, $bill_id, $round_up_savings = null) {

        $bindings = ["user" => $user, "bill" => $bill_id];

        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE bill_entry = :bill AND user =:user ";

        if (!is_null($round_up_savings)) {
            $sql .= "AND is_round_up_savings = :round_up_savings";
            $bindings["round_up_savings"] = $round_up_savings;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }


    public function getEntriesFromBill($bill_id) {

        $bindings = ["bill" => $bill_id];

        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE bill_entry = :bill ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function set_confirmed($id, $state) {
        $sql = "UPDATE " . $this->getTableName() . " SET is_confirmed =:state WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "state" => $state,
            "id" => $id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }
}
