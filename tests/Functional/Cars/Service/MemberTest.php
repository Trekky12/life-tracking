<?php

namespace Tests\Functional\Cars\Service;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class MemberTest extends BaseTestCase {

    protected $TEST_CAR_HASH = "ABCabc123";

    protected $uri_child_overview = "/cars/HASH/service/";
    protected $uri_child_edit = "/cars/HASH/service/edit/";
    protected $uri_child_save = "/cars/HASH/service/save/";
    protected $uri_child_delete = "/cars/HASH/service/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="service_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="gasolineForm" action="' . $this->getURIChildSave($this->TEST_CAR_HASH) . '" method="POST">', $body);
    }

    public function testPostAddElement() {

        $data = [
            "type" => 1,
            "date" => date('Y-m-d'),
            "mileage" => 1000,
            "service_oil_before" => 50,
            "service_oil_after" => 100,
            "service_water_wiper_before" => 50,
            "service_water_wiper_after" => 100,
            "service_air_front_left_before" => 1.1,
            "service_air_back_left_before" => 1.2,
            "service_air_front_right_before" => 1.3,
            "service_air_back_right_before" => 1.4,
            "service_air_front_left_after" => 2.1,
            "service_air_back_left_after" => 2.2,
            "service_air_front_right_after" => 2.3,
            "service_air_back_right_after" => 2.4,
            "service_tire_change" => 1,
            "service_garage" => 1,
            "notice" => "Test",
            "lat" => "10.00000000000000",
            "lng" => "5.00000000000000",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CAR_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_CAR_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostAddElement')]
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data, $this->TEST_CAR_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /** 
     * Edit created element
     */
    #[Depends('testAddedElement')]
    #[Depends('testPostAddElement')]
    public function testGetElementCreatedEdit(int $entry_id, array $data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH) . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"gasolineForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    #[Depends('testGetElementCreatedEdit')]
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "type" => 1,
            "date" => date('Y-m-d'),
            "mileage" => 1200,
            "service_oil_before" => 50,
            "service_oil_after" => 100,
            "service_water_wiper_before" => 50,
            "service_water_wiper_after" => 100,
            "service_air_front_left_before" => 1.1,
            "service_air_back_left_before" => 1.2,
            "service_air_front_right_before" => 1.3,
            "service_air_back_right_before" => 1.4,
            "service_air_front_left_after" => 2.1,
            "service_air_back_left_after" => 2.2,
            "service_air_front_right_after" => 2.3,
            "service_air_back_right_after" => 2.4,
            "service_tire_change" => 1,
            "service_garage" => 1,
            "notice" => "Test Updated",
            "lat" => "10.00000000000000",
            "lng" => "5.00000000000000",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CAR_HASH) . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_CAR_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostElementCreatedSave')]
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data, $this->TEST_CAR_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    #[Depends('testGetElementUpdated')]
    #[Depends('testPostElementCreatedSave')]
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH) . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    public function testJSTableFuel() {

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

        $response = $this->request('GET', '/cars/' . $this->TEST_CAR_HASH . '/service/table/?' . http_build_query($data));

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

    #[Depends('testGetElementUpdated')]
    public function testDeleteElement(int $entry_id) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CAR_HASH) . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data, $hash) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["date"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["mileage"] ?? '') . '<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
