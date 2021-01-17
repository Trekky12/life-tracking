<?php

namespace App\Domain\Notifications\Categories;

class NotificationCategoryMapper extends \App\Domain\Mapper {

    protected $table = 'notifications_categories';
    protected $dataobject = \App\Domain\Notifications\Categories\Category::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "notifications_categories_user";
    protected $element_name = "category";

    public function getCategoryByName($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE LOWER(name) = :name";

        $bindings = array("name" => strtolower($name));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function getCategoryByIdentifier($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE identifier = :name";

        $bindings = array("name" => strtolower($name));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function getUserCategories($sorted = null) {
        $sql = "SELECT DISTINCT t.* FROM " . $this->getTableName() . " t LEFT JOIN " . $this->getTableName($this->user_table) . " tu ";
        $sql .= " ON t.id = tu.{$this->element_name} ";
        $sql .= " WHERE tu.user = :user OR t.internal = 1 ";

        $bindings = array("user" => $this->user_id);

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new $this->dataobject($row);
        }
        return $results;
    }

}
