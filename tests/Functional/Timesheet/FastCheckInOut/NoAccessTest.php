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
    }

    /**
     * 
     */
    public function testPostTimesheetsFastCheckInNoAccess() {

        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckin($this->TEST_PROJECT_HASH), $data);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        
        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "error");
        $this->assertArrayHasKey("error", $json);
        $this->assertSame($json["error"], "Kein Zugriff erlaubt");
    }

    public function testPostTimesheetsFastCheckOutNoAccess() {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), $data);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "error");
        $this->assertArrayHasKey("error", $json);
        $this->assertSame($json["error"], "Kein Zugriff erlaubt");
    }

    /**
     * Delete
     */
    public function testDeleteTimesheet() {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_PROJECT_SHEET_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
