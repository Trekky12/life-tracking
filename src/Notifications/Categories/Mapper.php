<?php

namespace App\Notifications\Categories;

class Mapper extends \App\Base\Mapper {

    protected $table = 'notifications_categories';
    protected $model = '\App\Notifications\Categories\Category';
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getCategoryByName($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE LOWER(name) = :name";

        $bindings = array("name" => strtolower($name));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }
    
    public function getCategoryByIdentifier($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE identifier = :name";

        $bindings = array("name" => strtolower($name));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

}
