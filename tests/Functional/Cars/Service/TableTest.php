<?php

namespace Tests\Functional\Cars\Service;

use Tests\Functional\Base\BaseTestCase;

class TableTest extends BaseTestCase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testJSTableService() {

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

        $response = $this->request('GET', '/cars/service/table/?' . http_build_query($data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);

        $this->assertArrayHasKey("recordsTotal", $json);
        $this->assertArrayHasKey("recordsFiltered", $json);
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
    }
}
