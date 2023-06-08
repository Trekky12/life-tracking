<?php

namespace Tests\Functional\Recipes;

use Tests\Functional\Base\BaseTestCase;

class RecipesTestBase extends BaseTestCase {

    protected $uri_overview = "/recipes/cookbooks/";
    protected $uri_edit = "/recipes/cookbooks/edit/";
    protected $uri_save = "/recipes/cookbooks/save/";
    protected $uri_delete = "/recipes/cookbooks/delete/";
    protected $uri_view = "/recipes/cookbooks/HASH/view/";
    protected $uri_child_view = "/recipes/cookbooks/HASH1/view/HASH2";
    protected $uri_recipe_add_to_cookbook = "/recipes/HASH/addtocookbook";
    protected $uri_save_to_cookbook = "/recipes/cookbooks/addrecipe/";
    protected $uri_cookbook_recipes = "/recipes/list?count=100&start=0&query=&cookbook=HASH";
    protected $uri_remove_from_cookbook = "/recipes/cookbooks/HASH/removerecipe/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/recipes\/cookbooks\/(?<hash>.*)\/view\/">' . preg_quote($name) . '<\/a>\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/recipes\/cookbooks\/(?<hash>.*)\/view\/">(?<name>.*)<\/a>\s*<\/td>\s*(<td>[\s]*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    protected function getURIChildView($hash1, $hash2) {
        return str_replace("HASH2", $hash2, str_replace("HASH1", $hash1, $this->uri_child_view));
    }

    protected function getURIRecipeAddToCookbook($hash) {
        return str_replace("HASH", $hash, $this->uri_recipe_add_to_cookbook);
    }

    protected function getURICookbookRecipes($hash) {
        return str_replace("HASH", $hash, $this->uri_cookbook_recipes);
    }

    protected function getURIRecipeRemoveFromCookbook($hash) {
        return str_replace("HASH", $hash, $this->uri_remove_from_cookbook);
    }

}
