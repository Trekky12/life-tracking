<?php

namespace Tests\Functional\Recipes\Shoppinglist;

use Tests\Functional\Recipes\RecipesShoppinglistsTestBase;

class NoAccessTest extends RecipesShoppinglistsTestBase {

    protected $TEST_SHOPPINGLIST_ID = 1;
    protected $TEST_SHOPPINGLIST_HASH = "ABCabc123";

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
        $re = '/<tr>\s*<td><a href="\/recipes\/shoppinglists\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*<td>\s*<a href="(?<edit>.*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="(?<delete>.*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_SHOPPINGLIST_HASH) {
                $this->fail("Hash found");
            }
        }
    }

    /**
     * Edit project
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_SHOPPINGLIST_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * 
     */
    public function testPostParentSave() {

        $data = [
            "id" => $this->TEST_SHOPPINGLIST_ID,
            "hash" => $this->TEST_SHOPPINGLIST_HASH,
            "name" => "Test Shoppinglist Update",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_SHOPPINGLIST_ID, $data);

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Delete
     */
    public function testDeleteParent() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_SHOPPINGLIST_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * View Project
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_SHOPPINGLIST_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
