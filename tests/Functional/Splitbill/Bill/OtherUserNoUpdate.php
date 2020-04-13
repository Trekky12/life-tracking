<?php

namespace Tests\Functional\Splitbill\Bill;

use Tests\Functional\Splitbill\SplitbillTestBase;

class OtherUserNoUpdate extends SplitbillTestBase {

    protected $TEST_GROUP_ID = 1;
    protected $TEST_GROUP_HASH = "ABCabc123";
    protected $TEST_BILL_ID = 1;

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

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Create the Bill
     */
    public function testPostChildSave() {

        $data = [
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
