<?php

namespace App\Domain\Notifications\Clients;

class NotificationClientsMapper extends \App\Domain\Mapper {

    protected $table = 'notifications_clients';
    protected $dataobject = \App\Domain\Notifications\Clients\NotificationClient::class;
    protected $id = "id";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    private $client_table = "notifications_subscription_clients";

    public function getClientByEndpoint($endpoint) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE endpoint = :endpoint";

        $bindings = array("endpoint" => $endpoint);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function addCategory($client, $category, $object_id = null) {
        $bindings = array("category" => $category, "client" => $client, "object_id" => $object_id);

        $sql = "INSERT INTO " . $this->getTableName($this->client_table) . " (category, client, object_id) VALUES (:category, :client, :object_id)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function deleteCategory($client, $category, $object_id = null) {
        $sql = "DELETE FROM " . $this->getTableName($this->client_table) . "  WHERE client = :client AND category = :category";

        $bindings = [
            "category" => $category,
            "client" => $client
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

    public function getCategoriesFromEndpoint($endpoint) {
        $sql = "SELECT cc.category, cc.object_id FROM " . $this->getTableName($this->client_table) . " cc, " . $this->getTableName() . " c  WHERE c.id = cc.client AND c.endpoint = :endpoint";

        $bindings = array("endpoint" => $endpoint);

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

    public function getClientsByCategory($category) {
        $sql = "SELECT c.* FROM " . $this->getTableName() . " c, " . $this->getTableName($this->client_table) . " cc "
                . " WHERE c.id = cc.client AND cc.category = :category";

        $bindings = array("category" => $category);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getClientsByCategoryAndUser($category, $user, $object_id = null) {
        $sql = "SELECT c.* FROM " . $this->getTableName() . " c, " . $this->getTableName($this->client_table) . " cc "
                . " WHERE c.id = cc.client AND cc.category = :category AND c.user = :user";

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

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getClientByUserAndType($user, $type) {
        $sql = "SELECT * FROM " . $this->getTableName() . " c WHERE c.user = :user and type = :type LIMIT 1";

        $bindings = array("user" => $user, "type" => $type);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

}
