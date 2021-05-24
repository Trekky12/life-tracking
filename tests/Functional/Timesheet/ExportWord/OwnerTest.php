<?php

namespace Tests\Functional\Timesheet\ExportWord;

use Tests\Functional\Timesheet\TimesheetTestBase;

class OwnerTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Test Exporting 
     */
    public function testTimesheetsSheetsExport() {
        $response = $this->request('GET', $this->getURISheetsExportWord($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.wordprocessingml.document", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.docx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
    }

}
