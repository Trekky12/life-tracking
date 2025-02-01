<?php

namespace Tests\Functional\Timesheet\Timesheet;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessWithWrongParentTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_SHEET_ID = 6;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Access a specific child
     */
    public function testGetChildEditID() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $this->TEST_SHEET_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Update the sheet
     */
    public function testPostChildSaveID() {

        $data = [
            "id" => $this->TEST_SHEET_ID,
            "start" => date('Y-m-d') . " 12:00",
            "end" => date('Y-m-d') . " 14:10"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $this->TEST_SHEET_ID, $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete sheet
     */
    public function testDeleteChild() {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_SHEET_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
