<?php

namespace App\Domain\Notifications\Users;

class Mapper extends \App\Domain\Mapper {

    protected $table = "notifications_categories_users";

    public function addCategory($user, $category) {
        $bindings = array("category" => $category, "user" => $user);

        $sql = "INSERT INTO " . $this->getTableName($this->table) . " (category, user) VALUES (:category, :user)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function deleteCategory($user, $category) {
        $sql = "DELETE FROM " . $this->getTableName($this->table) . "  WHERE user = :user AND category = :category";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "category" => $category,
            "user" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getCategoriesByUser($user) {
        $sql = "SELECT category FROM " . $this->getTableName($this->table) . " WHERE user = :user";

        $bindings = array("user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    public function getUsersByCategory($category) {
        $sql = "SELECT user FROM " . $this->getTableName() . " WHERE category = :category";

        $bindings = array("category" => $category);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

}
