<?php

namespace Tests\Functional\Splitbill\Bill;

use Tests\Functional\Splitbill\SplitbillTestBase;

class OwnerTest extends SplitbillTestBase {

    protected $TEST_GROUP_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
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
    }

    /**
     * Create the Bill
     */
    public function testPostChildSave() {
        $data = [
            "name" => "Test",
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 50,
            "balance" => [
                1 => [
                    "paid" => "50.00",
                    "spend" => "0.00",
                    "paymethod" => 1
                ],
                2 => [
                    "paid" => "0.00",
                    "spend" => "50.00",
                    "paymethod" => null
                ]
            ],
            "notice" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH), $data);

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

        $row = $this->getChild($body, $data, $this->TEST_GROUP_HASH, 1);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Update Bills
     */

    /**
     * Edit Bill
     * @depends testGetChildCreated
     * @depends testPostChildSave
     */
    public function testGetChildCreatedEdit(int $bill_id, $data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $bill_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $bill_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"splitbillsBillsForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(int $bill_id) {

        $data = [
            "id" => $bill_id,
            "name" => "Testbill updated",
            "date" => date('Y-m-d'),
            "time" => "12:00:00",
            "value" => 50,
            "balance" => [
                1 => [
                    "paid" => "20.00",
                    "spend" => "40.00",
                    "paymethod" => 1
                ],
                2 => [
                    "paid" => "30.00",
                    "spend" => "10.00",
                    "paymethod" => null
                ]
            ],
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH) . $bill_id, $data);

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

        $row = $this->getChild($body, $data, $this->TEST_GROUP_HASH, 1);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetChildUpdated
     * @depends testPostChildCreatedSave
     */
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * Delete Bill
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(int $bill_id) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_GROUP_HASH) . $bill_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
