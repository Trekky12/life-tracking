<?php

namespace Tests\Functional\Recipes\CookbookRecipes;

use Tests\Functional\Recipes\RecipesCookbooksTestBase;

class MemberTest extends RecipesCookbooksTestBase {

    protected $TEST_COOKBOOK_HASH = "ABCabc123";
    protected $TEST_COOKBOOK_ID = 1;
    protected $TEST_RECIPE_HASH = "DEFdef456";
    protected $TEST_RECIPE_ID = 2;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetRecipeNotInCookbook() {
        $response = $this->request('GET', $this->getURIChildView($this->TEST_COOKBOOK_HASH, $this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testGetAddToCookbook() {
        $response = $this->request('GET', $this->getURIRecipeAddToCookbook($this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->uri_save_to_cookbook . "\" method=\"POST\">", $body);
    }

    /**
     * 
     */
    public function testPostAddToCookbook() {
        $data = [
            "cookbook" => $this->TEST_COOKBOOK_ID,
            "recipe" => $this->TEST_RECIPE_ID
        ];
        $response = $this->request('POST', $this->uri_save_to_cookbook, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/recipes/', $response->getHeaderLine("Location"));
    }

    /**
     * @depends testPostAddToCookbook
     */
    public function testGetCookbookRecipes() {
        $response = $this->request('GET', $this->getURICookbookRecipes($this->TEST_COOKBOOK_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString($this->TEST_RECIPE_HASH, $body);
    }

    public function testGetRecipeInCookbook() {
        $response = $this->request('GET', $this->getURIChildView($this->TEST_COOKBOOK_HASH, $this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringNotContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * Delete
     * @depends testGetCookbookRecipes
     */
    public function testRemoveRecipeFromCookbook() {
        $response = $this->request('DELETE', $this->getURIRecipeRemoveFromCookbook($this->TEST_COOKBOOK_HASH) . "?recipe=" . $this->TEST_RECIPE_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    public function testRemoveRecipeFromCookbook2() {
        $response = $this->request('DELETE', $this->getURIRecipeRemoveFromCookbook($this->TEST_COOKBOOK_HASH) . "?recipe=" . $this->TEST_RECIPE_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
    }

}
