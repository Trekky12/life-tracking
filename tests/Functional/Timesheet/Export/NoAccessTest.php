<?php

namespace Tests\Functional\Timesheet\Export;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetExportPage() {
        $response = $this->request('GET', $this->getURISheetsExportView($this->TEST_PROJECT_HASH));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Test Exporting 
     */
    public function testTimesheetsSheetsExportExcel() {
        $response = $this->request('GET', $this->getURISheetsExport($this->TEST_PROJECT_HASH), ["type" => "excel"]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    public function testTimesheetsSheetsExportWord() {
        $response = $this->request('GET', $this->getURISheetsExport($this->TEST_PROJECT_HASH), ["type" => "word"]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
