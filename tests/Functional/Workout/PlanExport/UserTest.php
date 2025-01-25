<?php

namespace Tests\Functional\Workout\PlanExport;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $TEST_PLAN_HASH = "ABCabc123";
    protected $uri_export = "/workouts/HASH/export/download";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testTimesheetsSheetsExport() {
        $response = $this->request('GET', $this->getURIExport($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.xlsx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
    }

    protected function getURIExport($hash) {
        return str_replace("HASH", $hash, $this->uri_export);
    }
}
