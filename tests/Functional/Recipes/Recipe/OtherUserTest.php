<?php

namespace Tests\Functional\Recipes\Recipe;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {

    protected $uri_overview = "/recipes/";
    protected $uri_edit = "/recipes/edit/";
    protected $uri_save = "/recipes/save/";
    protected $uri_delete = "/recipes/delete/";
    protected $uri_view = "/recipes/HASH/view";
    protected $TEST_RECIPE_ID = 1;
    protected $TEST_RECIPE_HASH = 'ABCabc123';

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testView() {
        $response = $this->request('GET', str_replace("HASH", $this->TEST_RECIPE_HASH, $this->uri_view));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** 
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_RECIPE_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }



    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_RECIPE_ID,
            "name" => "Test Recipe Updated"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_RECIPE_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_RECIPE_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
