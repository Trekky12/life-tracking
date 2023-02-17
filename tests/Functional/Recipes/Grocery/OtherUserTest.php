<?php

namespace Tests\Functional\Recipes\Grocery;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {

    protected $uri_overview = "/recipes/groceries/";
    protected $uri_edit = "/recipes/groceries/edit/";
    protected $uri_save = "/recipes/groceries/save/";
    protected $uri_delete = "/recipes/groceries/delete/";
    protected $TEST_GROCERY_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $matches = [];
        $re = '/<tbody>\s*<\/tbody>/';
        preg_match($re, $body, $matches);

        $this->assertFalse(!empty($matches));
    }

    /**
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_GROCERY_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * 
     */
    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_GROCERY_ID,
            "name" => "Test Grocery Updated",
            "unit" => "g"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_GROCERY_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_GROCERY_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
