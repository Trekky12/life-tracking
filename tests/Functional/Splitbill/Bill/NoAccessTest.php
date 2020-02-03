<?php

namespace Tests\Functional\Splitbill\Bill;

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
    public function testGetChildEdit() {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * Create the Bill
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $token) {

        $data = [
            "name" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH), array_merge($data, $token));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * Delete Bill
     * @depends testPostChildSave
     */
    public function testDeleteChild(array $token) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID, $token);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
