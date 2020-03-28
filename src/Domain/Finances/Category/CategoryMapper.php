<?php

namespace App\Domain\Finances\Category;

class CategoryMapper extends \App\Domain\Mapper {

    protected $table = 'finances_categories';
    protected $dataobject = \App\Domain\Finances\Category\Category::class;

    public function set_default($default) {
        $sql = "UPDATE " . $this->getTableName() . " SET is_default = :is_default WHERE id = :id";
        $bindings = array("id" => $default, "is_default" => 1);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function unset_default($default) {
        $sql = "UPDATE " . $this->getTableName() . " SET is_default = :is_default WHERE id != :id";
        $bindings = array("id" => $default, "is_default" => 0);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function get_default() {
        $sql = "SELECT id FROM " . $this->getTableName() . " WHERE is_default = :is_default";

        $bindings = array("is_default" => 1);
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

    public function getDefaultofUser($user) {
        $bindings = ["is_default" => 1, "user" => $user];

        $sql = "SELECT id FROM " . $this->getTableName() . " WHERE is_default = :is_default AND user =:user LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

}
