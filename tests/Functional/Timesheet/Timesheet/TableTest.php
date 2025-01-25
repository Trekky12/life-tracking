<?php

namespace Tests\Functional\Timesheet\Timesheet;

use Tests\Functional\Timesheet\TimesheetTestBase;

class TableTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testJSTable() {

        $data = [
            "from" => "2020-01-01",
            "to" => "2020-01-28",
            "searchQuery" => null,
            "sortColumn" => 0,
            "sortDirection" => "asc",
            "start" => 0,
            "length" => 10,
            "datatable" => 1
        ];

        $response = $this->request('GET', '/timesheets/' . $this->TEST_PROJECT_HASH . '/table/?' . http_build_query($data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);

        $this->assertArrayHasKey("recordsTotal", $json);
        $this->assertArrayHasKey("recordsFiltered", $json);
        $this->assertArrayHasKey("sum", $json);
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
    }
}
