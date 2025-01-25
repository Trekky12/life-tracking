<?php

namespace Tests\Functional\Timesheet\Timesheet;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_SHEET_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Add new Sheet
     */
    public function testGetChildEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
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
     * Create the sheet
     */
    public function testPostChildSave() {

        $data = [
            "start" => date('Y-m-d') . " 12:00",
            "end" => date('Y-m-d') . " 14:10"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH), $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Update the sheet
     */
    public function testPostChildSaveID() {

        $data = [
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
