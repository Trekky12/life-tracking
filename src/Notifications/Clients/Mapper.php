<?php

namespace App\Notifications\Clients;

class Mapper extends \App\Base\Mapper {

    protected $table = 'notifications_clients';
    protected $model = '\App\Notifications\Clients\NotificationClient';
    protected $id = "id";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    private $client_table = "notifications_categories_clients";

    public function getClientByEndpoint($endpoint) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE endpoint = :endpoint";

        $bindings = array("endpoint" => $endpoint);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function addCategory($client, $category) {
        $bindings = array("category" => $category, "client" => $client);

        $sql = "INSERT INTO " . $this->getTableName($this->client_table) . " (category, client) VALUES (:category, :client)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function deleteCategory($client, $category) {
        $sql = "DELETE FROM " . $this->getTableName($this->client_table) . "  WHERE client = :client AND category = :category";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "category" => $category,
            "client" => $client
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getCategoriesByEndpoint($endpoint) {
        $sql = "SELECT cc.category FROM " . $this->getTableName($this->client_table) . " cc, " . $this->getTableName() . " c  WHERE c.id = cc.client AND c.endpoint = :endpoint";

        $bindings = array("endpoint" => $endpoint);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
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
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function getClientsByCategoryAndUser($category, $user) {
        $sql = "SELECT c.* FROM " . $this->getTableName() . " c, " . $this->getTableName($this->client_table) . " cc "
                . " WHERE c.id = cc.client AND cc.category = :category AND c.user = :user";

        $bindings = array("category" => $category, "user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

}
