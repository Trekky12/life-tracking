<?php

namespace App\Domain\Recipes\Recipe;

class RecipeMapper extends \App\Domain\Mapper {

    protected $table = "recipes";
    protected $dataobject = \App\Domain\Recipes\Recipe\Recipe::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function deleteSteps($recipe_id) {
        $sql = "DELETE FROM " . $this->getTableName("recipes_steps") . "  WHERE recipe = :recipe";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "recipe" => $recipe_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addStep($recipe_id, $step = []) {
        return $this->addSteps($recipe_id, [$step]);
    }

    public function addSteps($recipe_id, $steps = []) {

        $data_array = array();
        $keys_array = array();
        foreach ($steps as $idx => $step) {
            $data_array["recipe" . $idx] = $recipe_id;
            $data_array["position" . $idx] = $step["position"];
            $data_array["name" . $idx] = $step["name"];
            $data_array["description" . $idx] = $step["description"];
            $data_array["preparation_time" . $idx] = $step["preparation_time"];
            $data_array["waiting_time" . $idx] = $step["waiting_time"];
            $data_array["createdBy" . $idx] = $this->user_id;
            $keys_array[] = "(:recipe" . $idx . ", :position" . $idx . ", :name" . $idx . ", :description" . $idx . ", :preparation_time" . $idx . ", :waiting_time" . $idx . ", :createdBy" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("recipes_steps") . " (recipe, position, name, description, preparation_time, waiting_time, createdBy) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getSteps($recipe_id) {
        $sql = "SELECT id, position, name, description, preparation_time, waiting_time FROM " . $this->getTableName("recipes_steps") . " WHERE recipe = :recipe ORDER BY position";

        $bindings = [
            "recipe" => $recipe_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row["id"]] = $row;
        }
        return $results;
    }

    public function addRecipeIngredients($recipe_id, $step_id, $ingredients = []) {

        $data_array = array();
        $keys_array = array();
        foreach ($ingredients as $idx => $ingredient) {
            $data_array["recipe" . $idx] = $recipe_id;
            $data_array["step" . $idx] = $step_id;
            $data_array["ingredient" . $idx] = $ingredient["ingredient"];
            $data_array["position" . $idx] = $ingredient["position"];
            $data_array["amount" . $idx] = $ingredient["amount"];
            $data_array["notice" . $idx] = $ingredient["notice"];
            $data_array["createdBy" . $idx] = $this->user_id;
            $keys_array[] = "(:recipe" . $idx . ", :step" . $idx . ", :ingredient" . $idx . ", :position" . $idx . ", :amount" . $idx . ", :notice" . $idx . ", :createdBy" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("recipes_recipe_ingredients") . " (recipe, step, ingredient, position, amount, notice, createdBy) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getRecipeIngredients($recipe_id) {
        $sql = "SELECT ri.id, ri.step, ri.ingredient, ri.position, ri.amount, ri.notice, i.name, i.unit "
                . "FROM " . $this->getTableName("recipes_recipe_ingredients") . " ri, " . $this->getTableName("recipes_ingredients") . " i "
                . "WHERE ri.ingredient = i.id AND recipe = :recipe ORDER BY ri.step, position";

        $bindings = [
            "recipe" => $recipe_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $step_id = $row["step"];
            if (!array_key_exists($step_id, $results)) {
                $results[$step_id] = [];
            }
            $results[$step_id][] = $row;
        }
        return $results;
    }

    public function getRecipesFromCookbook($cookbook_id, $sorted, $limit) {
        $sql = "SELECT r.* "
                . "FROM " . $this->getTableName("recipes_cookbook_recipes") . " cr, " . $this->getTableName("recipes") . " r "
                . "WHERE r.id  = cr.recipe AND cr.cookbook = :cookbook ";

        $sql .= " ORDER BY {$sorted}";
        $sql .= " LIMIT {$limit}";

        $bindings = [
            "cookbook" => $cookbook_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getRecipesFromCookbookCount($cookbook_id) {

        $sql = "SELECT COUNT(r.id) "
                . "FROM " . $this->getTableName("recipes_cookbook_recipes") . " cr, " . $this->getTableName("recipes") . " r "
                . "WHERE r.id  = cr.recipe AND cr.cookbook = :cookbook ";

        $bindings = [
            "cookbook" => $cookbook_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }
    
    public function getRecipesFiltered($sorted, $limit, $query = '') {
        $sql = "SELECT * FROM " . $this->getTableName();

        $bindings = array();

        $where = [];
        if (!empty($query)) {
            $where[] = "name like :query ";
            $bindings["query"] = '%'.$query.'%';
        }
        
        if(count($where) > 0){
            $sql .= " WHERE ".implode(" AND ", $where);
        }

        $sql .= " ORDER BY {$sorted}";
        $sql .= " LIMIT {$limit}";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getRecipesFilteredCount($query = '') {

        $sql = "SELECT COUNT({$this->id}) FROM " . $this->getTableName();

        $bindings = array();

        $where = [];
        
        if (!empty($query)) {
            $where[] = "name like :query";
            $bindings["query"] = '%'.$query.'%';
        }
        
        if(count($where) > 0){
            $sql .= " WHERE ".implode(" AND ", $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

}
