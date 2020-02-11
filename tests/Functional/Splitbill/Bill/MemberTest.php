<?php

namespace Tests\Functional\Splitbill\Bill;

use Tests\Functional\Splitbill\SplitbillTestBase;

class MemberTest extends SplitbillTestBase {

    protected $TEST_GROUP_HASH = "ABCabc123";
    
    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }
    /**
     * Add new Bill
     */
    public function testGetChildEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" id=\"splitbillsBillsForm\" action=\"" . $this->getURIChildSave($this->TEST_GROUP_HASH) . "\" method=\"POST\">", $body);
        
        return $this->extractFormCSRF($response);
    }

    /**
     * Create the Bill
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $csrf_data) {
        $data = [
            "name" => "Test",
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 50,
            "balance" => [
                1 => [
                    "paid" => 50,
                    "spend" => 0,
                    "paymethod" => 1
                ],
                2 => [
                    "paid" => 0,
                    "spend" => 50
                ]
            ],
            "notice" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));
        
        return $data;
    }

    /**
     * Is the created bill available?
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {

        $response = $this->request('GET', $this->getURIView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_GROUP_HASH, 2);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Update Bills
     */

    /**
     * Edit Bill
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(array $result_data_child) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $result_data_child["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_child["id"] . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"splitbillsBillsForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $result = [];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);
        
        return $result;
    }

    /**
     * 
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(array $result_data_child) {

        $data = [
            "id" => $result_data_child["id"],
            "name" => "Testbill updated",
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 50,
            "balance" => [
                1 => [
                    "paid" => 20,
                    "spend" => 40,
                    "paymethod" => 1
                ],
                2 => [
                    "paid" => 30,
                    "spend" => 10
                ]
            ],
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the bill data updated?
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $data) {

        $response = $this->request('GET', $this->getURIView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_GROUP_HASH, 2);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Delete Bill
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(array $result_data_child) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_GROUP_HASH) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

    }

}
