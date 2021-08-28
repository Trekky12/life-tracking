<?php

namespace App\Domain\Recipes\Ingredient;

class IngredientMapper extends \App\Domain\Mapper {

    protected $table = "recipes_ingredients";
    protected $dataobject = \App\Domain\Recipes\Ingredient\Ingredient::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

}
