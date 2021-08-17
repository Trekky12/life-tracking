<?php

namespace App\Domain\Recipes\Mealplan;

class MealplanMapper extends \App\Domain\Mapper {

    protected $table = "recipes_mealplans";
    protected $dataobject = \App\Domain\Recipes\Mealplan\Mealplan::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "recipes_mealplans_users";
    protected $element_name = "mealplan";

    public function addRecipe($mealplan_id, $recipe_id, $date, $position) {

        $sql = "INSERT INTO " . $this->getTableName("recipes_mealplans_recipes") . " (mealplan, recipe, date, position, createdBy) VALUES (:mealplan, :recipe, :date, :position, :createdBy)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "mealplan" => $mealplan_id,
            "recipe" => $recipe_id,
            "date" => $date,
            "position" => $position,
            "createdBy" => $this->user_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function moveRecipe($date, $position, $mealplan_recipe_id) {
        $sql = "UPDATE " . $this->getTableName("recipes_mealplans_recipes") . " SET date = :date, position = :position WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "date" => $date,
            "position" => $position,
            "id" => $mealplan_recipe_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return $mealplan_recipe_id;
    }

    public function removeRecipe($mealplan_recipe_id) {
        $sql = "DELETE FROM " . $this->getTableName("recipes_mealplans_recipes") . "  WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" => $mealplan_recipe_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }
    
    public function addRecipeNotice($mealplan_id, $notice, $date, $position) {

        $sql = "INSERT INTO " . $this->getTableName("recipes_mealplans_recipes") . " (mealplan, recipe, date, position, notice, createdBy) VALUES (:mealplan, :recipe, :date, :position, :notice, :createdBy)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "mealplan" => $mealplan_id,
            "recipe" => null,
            "date" => $date,
            "position" => $position,
            "notice" => $notice,
            "createdBy" => $this->user_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getMealplanRecipes($mealplan_id, $from, $to) {

        $sql = "SELECT mr.id, mr.date, mr.position, mr.notice, recipe.id as recipe_id, recipe.name as recipe_name, recipe.hash as recipe_hash "
                . "FROM " . $this->getTableName("recipes_mealplans_recipes") . " mr LEFT JOIN " . $this->getTableName("recipes") . " recipe ON mr.recipe = recipe.id "
                . "WHERE mealplan = :mealplan ";
        $sql .= " AND ( date BETWEEN :from AND :to ) ";
        $sql .= "order by position";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "mealplan" => $mealplan_id,
            "from" => $from,
            "to" => $to
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $date = $row["date"];
            if (!array_key_exists($date, $results)) {
                $results[$date] = [];
            }
            $results[$date][] = $row;
        }
        return $results;
    }

}
