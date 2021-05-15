<?php

namespace Tests\Functional\Recipes\Cookbook;

use Tests\Functional\Recipes\RecipesTestBase;

class NoAccessTest extends RecipesTestBase {

    protected $TEST_COOKBOOK_ID = 1;
    protected $TEST_COOKBOOK_HASH = "ABCabc123";
    protected $TEST_RECIPE_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/recipes\/cookbooks\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*<td>\s*<a href="(?<edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="(?<delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_COOKBOOK_HASH) {
                $this->fail("Hash found");
            }
        }
    }

    /**
     * Edit project
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_COOKBOOK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * 
     */
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
     * View Project
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_COOKBOOK_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * View recipe of cookbook
     */
    public function testGetViewRecipe() {
        $response = $this->request('GET', $this->getURIChildView($this->TEST_COOKBOOK_HASH, $this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
