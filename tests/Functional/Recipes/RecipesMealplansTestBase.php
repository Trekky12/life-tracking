<?php

namespace Tests\Functional\Recipes;

use Tests\Functional\Base\BaseTestCase;

class RecipesMealplansTestBase extends BaseTestCase {

    protected $uri_overview = "/recipes/mealplans/";
    protected $uri_edit = "/recipes/mealplans/edit/";
    protected $uri_save = "/recipes/mealplans/save/";
    protected $uri_delete = "/recipes/mealplans/delete/";
    protected $uri_view = "/recipes/mealplans/HASH/view/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/recipes\/mealplans\/(?<hash>.*)\/view\/">' . preg_quote($name) . '<\/a><\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/recipes\/mealplans\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*(<td>[\s]*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

}
