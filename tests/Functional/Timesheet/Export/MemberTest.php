<?php

namespace Tests\Functional\Timesheet\Export;

use Tests\Functional\Timesheet\TimesheetTestBase;

class MemberTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetExportPage() {
        $response = $this->request('GET', $this->getURISheetsExportView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString($this->getURISheetsExport($this->TEST_PROJECT_HASH), $body);
    }

    /**
     * Test Exporting 
     */
    public function testTimesheetsSheetsExportExcel() {
        $response = $this->request('GET', $this->getURISheetsExport($this->TEST_PROJECT_HASH), ["type" => "excel"]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.xlsx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
    }

    public function testTimesheetsSheetsExportWord() {
        $response = $this->request('GET', $this->getURISheetsExport($this->TEST_PROJECT_HASH), ["type" => "word"]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.wordprocessingml.document", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.docx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
    }

}
