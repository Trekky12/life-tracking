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

    /**
     * Test Exporting 
     */
    public function testTimesheetsSheetsExport() {

        $response = $this->request('GET', $this->getURISheetsExport($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.xlsx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
    }

}
