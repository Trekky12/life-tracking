<?php

namespace Tests\Functional\Splitbill\Bill;

use Tests\Functional\Splitbill\SplitbillTestBase;

class NoAccessWithWrongParentTest extends SplitbillTestBase {

    protected $TEST_GROUP_HASH = "DEFdef456";
    // Access a bill of this user in the other group
    // only bills of the same user can be accessed
    protected $TEST_BILL_ID = 4;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }


    /** 
     * Access specific bill
     */
    public function testGetChildEditID() {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Update the Bill
     */
    public function testPostChildSaveID() {

        $data = [
            "id" => $this->TEST_BILL_ID,
            "name" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID, $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete Bill
     */
    public function testDeleteChild() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
