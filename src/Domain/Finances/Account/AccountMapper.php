<?php

namespace App\Domain\Finances\Account;

class AccountMapper extends \App\Domain\Mapper
{

    protected $table = 'finances_accounts';
    protected $dataobject = \App\Domain\Finances\Account\Account::class;

    public function addValue($id, $value)
    {
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

    public function substractValue($id, $value)
    {
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
}
