<?php

namespace App\Domain\Recipes\Shoppinglist;

class ShoppinglistMapper extends \App\Domain\Mapper
{

    protected $table = "recipes_shoppinglists";
    protected $dataobject = \App\Domain\Recipes\Shoppinglist\Shoppinglist::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "recipes_shoppinglists_users";
    protected $element_name = "shoppinglist";

    public function addGrocery($shoppinglist_id, $grocery_id, $amount, $unit, $notice, $position)
    {

        $sql = "INSERT INTO " . $this->getTableName("recipes_shoppinglists_entries") . " 
                    (shoppinglist, grocery, amount, unit, notice, position, createdBy) 
                VALUES (:shoppinglist, :grocery, :amount, :unit, :notice, :position, :createdBy)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "shoppinglist" => $shoppinglist_id,
            "grocery" => $grocery_id,
            "amount" => $amount,
            "unit" => $unit,
            "notice" => $notice,
            "position" => $position,
            "createdBy" => $this->user_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getShoppingListEntries($shoppinglist_id, $limit = null, $done = null)
    {
        $sql = "SELECT se.id, se.amount, se.unit, g.name as grocery, se.notice, se.position, se.done 
                    FROM " . $this->getTableName("recipes_shoppinglists_entries") . " se,  
                         " . $this->getTableName("recipes_groceries") . " g  
                    WHERE se.grocery = g.id AND shoppinglist = :shoppinglist";

        if (!is_null($done)) {
            $sql .= $done ? " AND done IS NOT NULL " : " AND done is NULL ";
        } else {
            $sql .= " AND (done IS NULL OR DATEDIFF(NOW(), done) < 1) ";
        }

        $bindings = [
            "shoppinglist" => $shoppinglist_id
        ];

        $sql .= " ORDER BY se.position ASC, se.createdOn DESC";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }

    public function getShoppingListEntriesCount($shoppinglist_id, $done = null)
    {

        $sql = "SELECT COUNT(id) FROM " . $this->getTableName("recipes_shoppinglists_entries") . " WHERE shoppinglist = :shoppinglist";

        if (!is_null($done)) {
            $sql .= $done ? " AND done IS NOT NULL " : " AND done is NULL ";
        } else {
            $sql .= " AND (done IS NULL OR DATEDIFF(NOW(), done) < 1) ";
        }

        $bindings = [
            "shoppinglist" => $shoppinglist_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function set_state($id, $shoppinglist_id, $state)
    {
        $sql = "UPDATE " . $this->getTableName("recipes_shoppinglists_entries") . " SET done = :done WHERE id=:id AND shoppinglist = :shoppinglist";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "done" => $state > 0 ? date('Y-m-d H:i:s') : null,
            "id" => $id,
            "shoppinglist" => $shoppinglist_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function deleteGrocery($shoppinglist_id, $id)
    {

        $sql = "DELETE FROM " . $this->getTableName("recipes_shoppinglists_entries") . "  WHERE id = :id AND shoppinglist = :shoppinglist";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" => $id,
            "shoppinglist" => $shoppinglist_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }
}
