<?php

namespace Tests\Functional\Finances\Budget;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {

    protected $uri_overview = "/finances/budgets/";
    protected $uri_edit = "/finances/budgets/edit/";
    protected $uri_save = "/finances/budgets/saveAll";
    protected $uri_delete = "/finances/budgets/delete/";
    protected $TEST_BUDGET_ENTRY_REGULAR = 1;
    protected $TEST_BUDGET_ENTRY_REST = 2;

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
        $re = '/<div class="page-content">\s*<\/div>/';
        preg_match($re, $body, $matches);

        $this->assertFalse(empty($matches));
    }

    public function testGetAddElements() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $matches = [];
        $re = '/<input required type=\"number\" class=\"form-control value\" id=\"inputValue_[0-9]+\" name="budget\[[0-9]+\]\[value\]" step="any" value="">/';
        preg_match($re, $body, $matches);

        $this->assertFalse(empty($matches));
    }

    /**
     * 
     */
    public function testPostElementCreatedSave() {

        $data = [
            "budget" => [
                [
                    "description" => "Rest",
                    "id" => $this->TEST_BUDGET_ENTRY_REST,
                    "value" => 2
                ],
                [
                    "description" => "Test Budget Updated",
                    "category" => [
                        1
                    ],
                    "value" => 8,
                    "hidden" => 0,
                    "id" => $this->TEST_BUDGET_ENTRY_REGULAR
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_BUDGET_ENTRY_REGULAR);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Element nicht gefunden", $json["error"]);
    }

}
