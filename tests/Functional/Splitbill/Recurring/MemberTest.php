<?php

namespace Tests\Functional\Splitbill\Recurring;

use Tests\Functional\Splitbill\SplitbillTestBase;

class MemberTest extends SplitbillTestBase {

    protected $TEST_GROUP_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIRecurringView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="splitbills_bills_recurring_table"', $body);
    }

    /**
     * Add new recurring bill
     */
    public function testGetRecurringEdit() {
        $response = $this->request('GET', $this->getURIRecurringEdit($this->TEST_GROUP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" id=\"splitbillsBillsForm\" action=\"" . $this->getURIRecurringSave($this->TEST_GROUP_HASH) . "\" method=\"POST\">", $body);
    }

    /**
     * Create the Bill
     */
    public function testPostRecurringSave() {
        $data = [
            "name" => "Test",
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
            "notice" => "Test",
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 1,
            "is_active" => 1
        ];
        $response = $this->request('POST', $this->getURIRecurringSave($this->TEST_GROUP_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIRecurringView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the created bill available?
     * @depends testPostRecurringSave
     */
    public function testGetRecurringCreated(array $data) {

        $response = $this->request('GET', $this->getURIRecurringView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        
        $row = $this->getRecurring($body, $data, $this->TEST_GROUP_HASH, 2);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Update Bills
     */

    /**
     * Edit Bill
     * @depends testGetRecurringCreated
     * @depends testPostRecurringSave
     */
    public function testGetRecurringCreatedEdit(int $bill_id, $data) {

        $response = $this->request('GET', $this->getURIRecurringEdit($this->TEST_GROUP_HASH) . $bill_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $bill_id . "\">", $body);

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
     * @depends testGetRecurringCreatedEdit
     */
    public function testPostRecurringCreatedSave(int $bill_id) {

        $data = [
            "id" => $bill_id,
            "name" => "Testbill updated",
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
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 2,
            "is_active" => 1
        ];
        $response = $this->request('POST', $this->getURIRecurringSave($this->TEST_GROUP_HASH) . $bill_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIRecurringView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the bill data updated?
     * @depends testPostRecurringCreatedSave
     */
    public function testGetRecurringUpdated(array $data) {

        $response = $this->request('GET', $this->getURIRecurringView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getRecurring($body, $data, $this->TEST_GROUP_HASH, 2);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetRecurringUpdated
     * @depends testPostRecurringCreatedSave
     */
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->getURIRecurringEdit($this->TEST_GROUP_HASH) . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * Delete Bill
     * @depends testGetRecurringUpdated
     */
    public function testDeleteRecurring(int $bill_id) {

        $response = $this->request('DELETE', $this->getURIRecurringDelete($this->TEST_GROUP_HASH) . $bill_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
