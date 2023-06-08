<?php

namespace App\Domain\Notifications;

class MailNotificationUsersMapper extends \App\Domain\Mapper {

    protected $table = "mail_subscription_users";

    public function addCategory($user, $category, $object_id = null) {
        $bindings = array("category" => $category, "user" => $user, "object_id" => $object_id);

        $sql = "INSERT INTO " . $this->getTableName($this->table) . " (category, user, object_id) VALUES (:category, :user, :object_id)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function deleteCategory($user, $category, $object_id = null) {
        $sql = "DELETE FROM " . $this->getTableName($this->table) . "  WHERE user = :user AND category = :category";

        $bindings = [
            "category" => $category,
            "user" => $user
        ];

        if (!is_null($object_id)) {
            $sql .= " AND object_id = :object_id";
            $bindings["object_id"] = $object_id;
        }
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getCategoriesByUser($user) {
        $sql = "SELECT category, object_id FROM " . $this->getTableName($this->table) . " WHERE user = :user";

        $bindings = array("user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $category = $row["category"];

            $val = $category;
            if (!is_null($row["object_id"])) {
                $val = sprintf("%s_%s", $category, $row["object_id"]);
            }
            $results[] = $val;
        }
        return $results;
    }

    public function doesUserHaveCategory($category, $user, $object_id = null) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE category = :category AND user = :user";

        $bindings = [
            "category" => $category,
            "user" => $user
        ];

        if (!is_null($object_id)) {
            $sql .= " AND object_id = :object_id";
            $bindings["object_id"] = $object_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

}
