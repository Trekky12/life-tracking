<?php

namespace Tests\Functional\Splitbill;

class SplitbillOwnerTest extends SplitbillTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testRedirect() {
        $response = $this->runApp('GET', '/splitbills/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testGetOverview() {
        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("splitbills_groups_table", $body);
    }

    public function testGetParentEdit() {
        $response = $this->runApp('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->uri_save . "\" method=\"POST\">", $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * @depends testGetParentEdit
     */
    public function testPostParentSave(array $csrf_data) {
        $data = ["name" => "Testgroup", "users" => [10]];
        $response = $this->runApp('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostParentSave
     */
    public function testGetParentCreated($data) {
        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getParent($body, $data["name"]);

        $this->assertArrayHasKey("hash", $row);
        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["hash"] = $row["hash"];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Edit group
     * @depends testPostParentSave
     * @depends testGetParentCreated
     */
    public function testGetParentCreatedEdit($data, array $result_data) {

        $response = $this->runApp('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\"  type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\" value=\"" . $data["name"] . "\">", $body);


        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">.*<input name="hash"  type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        $this->assertArrayHasKey("hash", $matches);

        $result = [];
        $result["hash"] = $matches["hash"];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetParentCreatedEdit
     */
    public function testPostParentCreatedSave(array $result_data) {
        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testgroup Updated", "users" => [1, 10]];
        $response = $this->runApp('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    /**
     * View Group
     * @depends testGetParentCreated
     */
    public function testGetViewParent(array $result_data) {
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("splitbills_bills_table", $body);
    }

    /**
     * Bills
     */

    /**
     * Add new bill
     * @depends testGetParentCreated
     */
    public function testGetChildEdit($result) {
        $response = $this->runApp('GET', $this->getURIChildEdit($result["hash"]));
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
                1 => [
                    "paid" => 5,
                    "spend" => 0,
                    "paymethod" => 1
                ],
                10 => [
                    "paid" => 5,
                    "spend" => 10
                ]
            ],
            "notice" => "Test",
            "sbgroup" => $result["id"]
        ];
        $response = $this->runApp('POST', $this->getURIChildSave($result["hash"]), array_merge($data, $csrf_data));

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
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

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

        $response = $this->runApp('GET', $this->getURIChildEdit($result_data_parent["hash"]) . $result_data_child["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_child["id"] . "\">", $body);
        $this->assertStringContainsString("<input name=\"sbgroup\" type=\"hidden\" value=\"" . $result_data_parent["id"] . "\">", $body);

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
                1 => [
                    "paid" => 5,
                    "spend" => 5,
                    "paymethod" => 1
                ],
                10 => [
                    "paid" => 10,
                    "spend" => 10
                ]
            ]
        ];

        $response = $this->runApp('POST', $this->getURIChildSave($result_data_parent["hash"]) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

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
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

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
        $response = $this->runApp('DELETE', $this->getURIChildDelete($result_data_parent["hash"]) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * Delete / clean
     * @depends testGetParentCreated
     */
    public function testDeleteParent(array $result_data) {
        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * Delete parent with childs
     */
    public function testDeleteParentWithChilds() {

        // Open Parent Add page
        $response1 = $this->runApp('GET', $this->uri_edit);
        $csrf1 = $this->extractFormCSRF($response1);

        // Add Parent
        $data1 = ["name" => "Test delete with childs", "users" => [10]];
        $this->runApp('POST', $this->uri_save, array_merge($data1, $csrf1));

        // get Hash/ID from Overview
        $response3 = $this->runApp('GET', $this->uri_overview);
        $row = $this->getParent((string) $response3->getBody(), $data1["name"]);

        $parent_hash = $row["hash"];
        $parent_id = $row["id_edit"];

        // Open Child Add Page
        $response4 = $this->runApp('GET', $this->getURIChildEdit($parent_hash));
        $csrf3 = $this->extractFormCSRF($response4);

        // Add Child
        $data2 = ["name" => "Test Child", "sbgroup" => $parent_id];
        $response5 = $this->runApp('POST', $this->getURIChildSave($parent_hash), array_merge($data2, $csrf3));
        $this->assertEquals(301, $response5->getStatusCode());
        $this->assertEquals($this->getURIView($parent_hash), $response5->getHeaderLine("Location"));

        // Get CSRF From Overview
        $response6 = $this->runApp('GET', $this->uri_overview);
        $csrf4 = $this->extractJSCSRF($response6);

        // Delete Parent
        $response = $this->runApp('DELETE', $this->uri_delete . $parent_id, $csrf4);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
