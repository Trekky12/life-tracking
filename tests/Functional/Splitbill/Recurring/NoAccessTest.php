<?php

namespace Tests\Functional\Splitbill\Recurring;

use Tests\Functional\Splitbill\SplitbillTestBase;

class NoAccessTest extends SplitbillTestBase {

    protected $TEST_GROUP_ID = 1;
    protected $TEST_GROUP_HASH = "ABCabc123";
    protected $TEST_BILL_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Add new Bill
     */
    public function testGetRecurringEdit() {

        $response = $this->request('GET', $this->getURIRecurringEdit($this->TEST_GROUP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Create the Bill
     */
    public function testPostRecurringSave() {

        $data = [
            "name" => "Test"
        ];
        $response = $this->request('POST', $this->getURIRecurringSave($this->TEST_GROUP_HASH), $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Delete Bill
     */
    public function testDeleteRecurring() {

        $response = $this->request('DELETE', $this->getURIRecurringDelete($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
