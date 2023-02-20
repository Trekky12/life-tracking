<?php

namespace App\Domain\Recipes\Grocery;

class GroceryMapper extends \App\Domain\Mapper
{

    protected $table = "recipes_groceries";
    protected $dataobject = \App\Domain\Recipes\Grocery\Grocery::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getGroceriesFromInput($grocery_input, $is_food = null)
    {

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

    public function getGroceryByName($grocery_input, $is_food = null)
    {

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
}
