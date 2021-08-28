<?php

namespace App\Domain\Recipes\Cookbook;

class CookbookMapper extends \App\Domain\Mapper {

    protected $table = "recipes_cookbooks";
    protected $dataobject = \App\Domain\Recipes\Cookbook\Cookbook::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "recipes_cookbooks_users";
    protected $element_name = "cookbook";

    public function addRecipe($cookbook_id, $recipe_id) {

        $sql = "INSERT INTO " . $this->getTableName("recipes_cookbook_recipes") . " (cookbook, recipe, createdBy) VALUES (:cookbook, :recipe, :createdBy)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "cookbook" => $cookbook_id,
            "recipe" => $recipe_id,
            "createdBy" => $this->user_id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function removeRecipe($cookbook_id, $recipe_id) {
        $sql = "DELETE FROM " . $this->getTableName("recipes_cookbook_recipes") . "  WHERE cookbook = :cookbook AND recipe = :recipe";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "cookbook" => $cookbook_id,
            "recipe" => $recipe_id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getCookbookRecipes($cookbook_id) {

        $sql = "SELECT recipe FROM " . $this->getTableName("recipes_cookbook_recipes") . " WHERE cookbook = :cookbook";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "cookbook" => $cookbook_id
        ]);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

}
