<?php

namespace App\Domain\Recipes\Grocery;

class GroceryMapper extends \App\Domain\Mapper {

    protected $table = "recipes_groceries";
    protected $dataobject = \App\Domain\Recipes\Grocery\Grocery::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getGroceriesFromInput($grocery_input, $is_food = null) {

        $sql = "SELECT * FROM " . $this->getTableName() . " 
                WHERE name LIKE :grocery_input ";
        if ($is_food) {
            $sql .= " AND is_food = 1 ";
        }
        $sql .= "ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "grocery_input" => "%" . $grocery_input . "%"
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getGroceryByName($grocery_input, $is_food = null) {

        $sql = "SELECT * FROM " . $this->getTableName() . " 
                WHERE name = :grocery_input ";
        if ($is_food) {
            $sql .= " AND is_food = 1 ";
        }
        $sql .= "ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "grocery_input" => $grocery_input
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function mergeGroceries($grocery1, $grocery2) {

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE " . $this->getTableName("recipes_recipe_ingredients") . " SET ingredient = :grocery1 WHERE ingredient = :grocery2";
            $bindings = ["grocery1" => $grocery1, "grocery2" => $grocery2];
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($bindings);

            if (!$result) {
                throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
            }

            $sql2 = "UPDATE " . $this->getTableName("recipes_shoppinglists_entries") . " SET grocery = :grocery1 WHERE grocery = :grocery2";
            $bindings2 = ["grocery1" => $grocery1, "grocery2" => $grocery2];
            $stmt2 = $this->db->prepare($sql2);
            $result2 = $stmt2->execute($bindings2);

            if (!$result2) {
                throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
            }

            $sql3 = "DELETE FROM " . $this->getTableName() . " WHERE id = :grocery2";
            $bindings3 = ["grocery2" => $grocery2];
            $stmt3 = $this->db->prepare($sql3);
            $result3 = $stmt3->execute($bindings3);

            if (!$result3) {
                throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();

            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }
}
