<?php

namespace App\Domain\Finances\Account;

class AccountMapper extends \App\Domain\Mapper {

    protected $table = 'finances_accounts';
    protected $dataobject = \App\Domain\Finances\Account\Account::class;

    public function addValue($id, $value) {
        $sql = "UPDATE " . $this->getTableName() . " SET value = value  + :value WHERE id  = :id";
        $bindings = [
            "id" => $id,
            "value" => $value
        ];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function substractValue($id, $value) {
        $sql = "UPDATE " . $this->getTableName() . " SET value = value  - :value WHERE id  = :id";
        $bindings = [
            "id" => $id,
            "value" => $value
        ];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getofUser($id, $user) {
        $bindings = ["id" => $id, "user" => $user];

        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE id = :id AND user =:user LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function getAllfromUsers($users = [], $sorted = false) {

        $user_ids = array_keys($users);

        if (empty($user_ids)) {
            return [];
        }
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE user IN (" . implode(',', $user_ids) . ")";

        $bindings = array();

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = $row["user"];
            $results[$key][] = new $this->dataobject($row);
        }
        return $results;
    }
}
