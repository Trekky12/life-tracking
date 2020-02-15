<?php

namespace Tests\Functional\Finances\Recurring;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {

    protected $uri_overview = "/finances/recurring/";
    protected $uri_edit = "/finances/recurring/edit/";
    protected $uri_save = "/finances/recurring/save/";
    protected $uri_delete = "/finances/recurring/delete/";
    protected $TEST_FINANCE_RECURRING_ID = 1;

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

        $this->assertFalse(empty($matches));
    }

    /**
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_FINANCE_RECURRING_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }

    /**
     * 
     */
    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_FINANCE_RECURRING_ID,
            "type" => 0,
            "category" => null,
            "description" => "Test Updated",
            "value" => 20,
            "paymethod" => "",
            "common" => 0,
            "common_value" => "",
            "notice" => "testing",
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 3
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_FINANCE_RECURRING_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_FINANCE_RECURRING_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Element nicht gefunden", $json["error"]);
    }

}
