<?php

namespace Tests\Functional\Timesheet\FastCheckInOut;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessTest extends TimesheetTestBase {

    protected $TEST_PROJECT_ID = 1;
    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_PROJECT_SHEET_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetTimesheetsFastNoAccess() {
        $response = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * @depends testGetTimesheetsFastNoAccess
     */
    public function testPostTimesheetsFastCheckInNoAccess(array $token) {

        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckin($this->TEST_PROJECT_HASH), array_merge($data, $token));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "error");
        $this->assertArrayHasKey("message", $json);
        $this->assertSame($json["message"], "Kein Zugriff erlaubt");
    }

    public function testPostTimesheetsFastCheckOutNoAccess() {
        $response1 = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));
        $token = $this->extractJSCSRF($response1);

        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), array_merge($data, $token));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "error");
        $this->assertArrayHasKey("message", $json);
        $this->assertSame($json["message"], "Kein Zugriff erlaubt");
    }

    /**
     * Delete
     */
    public function testDeleteTimesheet() {
        $response1 = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));
        $token = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_PROJECT_SHEET_ID, $token);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
