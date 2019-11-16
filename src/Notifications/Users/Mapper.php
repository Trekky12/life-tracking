<?php

namespace App\Notifications\Users;

class Mapper extends \App\Base\Mapper {

    protected $table = "notifications_categories_users";

    public function addCategory($user, $category) {
        $bindings = array("category" => $category, "user" => $user);

        $sql = "INSERT INTO " . $this->getTable($this->table) . " (category, user) VALUES (:category, :user)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function deleteCategory($user, $category) {
        $sql = "DELETE FROM " . $this->getTable($this->table) . "  WHERE user = :user AND category = :category";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "category" => $category,
            "user" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getCategoriesByUser($user) {
        $sql = "SELECT category FROM " . $this->getTable($this->table) . " WHERE user = :user";

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
        $sql = "SELECT user FROM " . $this->getTable() . " WHERE category = :category";

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