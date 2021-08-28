<?php

namespace Tests\Functional\Recipes\Cookbook;

use Tests\Functional\Recipes\RecipesCookbooksTestBase;

class OwnerNoViewTest extends RecipesCookbooksTestBase {

    protected $TEST_COOKBOOK_HASH = "DEFdef456";
    protected $TEST_RECIPE_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * View Project (owner -> has no access to view)
     */
    public function testGetViewParentOwner() {
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
