<?php

namespace Tests\Functional\Finances\Budget;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/finances/budgets/";
    protected $uri_edit = "/finances/budgets/edit/";
    protected $uri_save = "/finances/budgets/saveAll";
    protected $uri_delete = "/finances/budgets/delete/";
    protected $TEST_CATEGORY_ID = "2";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<h2>Budget</h2>', $body);
    }

    public function testGetAddElements() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<h2>Budget</h2>', $body);
    }

    /**
     * 
     */
    public function testPostAddElements() {

        $data = [
            "budget" => [
                [
                    "description" => "Test Budget",
                    "category" => [
                        $this->TEST_CATEGORY_ID
                    ],
                    "value" => "5.00",
                    "is_hidden" => 0
                ],
                [
                    "description" => "Rest",
                    "id" => null,
                    "value" => "5.00",
                    "is_remaining" => 1
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElements
     */
    public function testAddedElements($data) {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $rows = $this->checkBudget($body, $data);

        $result = [];
        $result["id_regular"] = $rows[0]["id"];
        $result["id_rest"] = $rows[1]["id"];

        return $result;
    }

    /**
     * @depends testAddedElements
     */
    public function testPostUpdateElements($result_data) {

        $data = [
            "budget" => [
                [
                    "id" => $result_data["id_regular"],
                    "description" => "Test Budget Updated",
                    "value" => "8.00",
                    "is_hidden" => 0,
                    "category" => [
                        $this->TEST_CATEGORY_ID
                    ]
                ],
                [
                    "is_remaining" => "1",
                    "id" => $result_data["id_rest"],
                    "description" => "Rest",
                    "value" => "2.00",
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostUpdateElements
     */
    public function testChanges(array $data) {
        $response = $this->request('GET', $this->uri_edit);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testPostUpdateElements
     */
    public function testGetElementsUpdated(array $data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $rows = $this->checkBudget($body, $data);

        $result = [];
        $result["id_regular"] = $rows[0]["id"];
        $result["id_rest"] = $rows[1]["id"];

        return $result;
    }

    /**
     * @depends testGetElementsUpdated
     */
    public function testDeleteElement1($result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id_regular"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    /**
     * @depends testGetElementsUpdated

     */
    public function testDeleteElement2($result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id_rest"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementsInTable($body) {
        $matches = [];
        $re = '/<div class=\"budget-entry\" data-budget="(?<value>[0-9.]+)">\s*<h3><a href="\/finances\/stats\/budget\/(?<id>[0-9]+)">(?<name>.*)<\/a><\/h3>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    private function checkBudget($body, $data) {
        $rows = $this->getElementsInTable($body);
        
        $this->assertEquals(2, count($rows));

        // check first (regular) entry
        $expected_value1 = number_format($rows[0]["value"], 2);
        $real_value1 = number_format($data["budget"][0]["value"], 2);
        $this->assertEquals($expected_value1, $real_value1);

        $expected_name1 = $rows[0]["name"];
        $real_name1 = $data["budget"][0]["description"];
        $this->assertEquals($expected_name1, $real_name1);

        $this->assertArrayHasKey("id", $rows[0]);

        // check last ("Rest") entry
        $expected_value2 = number_format($rows[1]["value"], 2);
        $real_value2 = number_format($data["budget"][1]["value"], 2);
        $this->assertEquals($expected_value2, $real_value2);

        $expected_name2 = $rows[1]["name"];
        $real_name2 = $data["budget"][1]["description"];
        $this->assertEquals($expected_name2, $real_name2);

        $this->assertArrayHasKey("id", $rows[1]);

        return $rows;
    }

}
