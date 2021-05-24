<?php

namespace Tests\Functional\Timesheet\ExportWord;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Test Exporting 
     */
    public function testTimesheetsSheetsExport() {
        $response = $this->request('GET', $this->getURISheetsExportWord($this->TEST_PROJECT_HASH));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
