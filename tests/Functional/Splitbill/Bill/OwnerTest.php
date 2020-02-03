<?php

namespace Tests\Functional\Splitbill\Bill;

use Tests\Functional\Splitbill\SplitbillTestBase;

class OwnerTest extends SplitbillTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }
    
    /**
     * Add new bill
     * @depends testGetParentCreated
     */
    public function testGetChildEdit($result) {
        $response = $this->request('GET', $this->getURIChildEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" id=\"splitbillsBillsForm\" action=\"" . $this->getURIChildSave($result["hash"]) . "\" method=\"POST\">", $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the Bill
     * @depends testGetParentCreated
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $result, array $csrf_data) {
        $data = [
            "name" => "Testbill",
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 10,
            "balance" => [
                3 => [
                    "paid" => 5,
                    "spend" => 0,
                    "paymethod" => 1
                ],
                1 => [
                    "paid" => 5,
                    "spend" => 10
                ]
            ],
            "notice" => "Test",
            "sbgroup" => $result["id"]
        ];
        $response = $this->request('POST', $this->getURIChildSave($result["hash"]), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result["hash"]), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the created bill visible?
     * @depends testGetParentCreated
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $result_data, array $data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getChild($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Update Bill
     */

    /**
     * Edit Bill
     * @depends testGetParentCreated
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(array $result_data_parent, array $result_data_child) {

        $response = $this->request('GET', $this->getURIChildEdit($result_data_parent["hash"]) . $result_data_child["id"]);

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
     * @depends testGetParentCreated
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(array $result_data_parent, array $result_data_child) {
        $data = [
            "id" => $result_data_child["id"],
            "name" => "Testbill Updated",
            "sbgroup" => $result_data_parent["id"],
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 15,
            "balance" => [
                3 => [
                    "paid" => 5,
                    "spend" => 5,
                    "paymethod" => 1
                ],
                1 => [
                    "paid" => 10,
                    "spend" => 10
                ]
            ]
        ];

        $response = $this->request('POST', $this->getURIChildSave($result_data_parent["hash"]) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result_data_parent["hash"]), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the bill data updated?
     * @depends testGetParentCreated
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $result_data, array $data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }


    /**
     * Delete bill
     * @depends testGetParentCreated
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(array $result_data_parent, array $result_data_child) {
        $response = $this->request('DELETE', $this->getURIChildDelete($result_data_parent["hash"]) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
