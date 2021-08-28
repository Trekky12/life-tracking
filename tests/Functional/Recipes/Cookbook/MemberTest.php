<?php

namespace Tests\Functional\Recipes\Cookbook;

use Tests\Functional\Recipes\RecipesCookbooksTestBase;

class MemberTest extends RecipesCookbooksTestBase {

    protected $TEST_COOKBOOK_ID = 1;
    protected $TEST_COOKBOOK_HASH = "ABCabc123";
    protected $TEST_RECIPE_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = $this->getParents($body);
        $hashs = array_map(function($match) {
            return $match["hash"];
        }, $matches);
        $this->assertContains($this->TEST_COOKBOOK_HASH, $hashs);
    }

    /**
     * Edit trip
     * 
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_COOKBOOK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    public function testPostParentSave() {
        $data = [
            "id" => $this->TEST_COOKBOOK_ID,
            "hash" => $this->TEST_COOKBOOK_HASH,
            "name" => "Test Cookbook Update",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_COOKBOOK_ID, $data);

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Delete
     */
    public function testDeleteParent() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_COOKBOOK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * View (members can access)
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_COOKBOOK_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div id="recipes_list" data-cookbook=', $body);
    }
    
    /**
     * View recipe of cookbook (members can access)
     */
    public function testGetViewRecipe() {
        $response = $this->request('GET', $this->getURIChildView($this->TEST_COOKBOOK_HASH, $this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div class="recipe-description">', $body);
    }

}
