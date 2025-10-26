<?php

namespace Tests\Functional\Cars\Fuel;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class OwnerTest extends BaseTestCase {

    protected $TEST_CAR_HASH = "ABCabc123";

    protected $uri_child_overview = "/cars/HASH/refuel/";
    protected $uri_child_edit = "/cars/HASH/refuel/edit/";
    protected $uri_child_save = "/cars/HASH/refuel/save/";
    protected $uri_child_delete = "/cars/HASH/refuel/delete/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="fuel_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="gasolineForm" action="' . $this->getURIChildSave($this->TEST_CAR_HASH) . '" method="POST">', $body);
    }

    public function testPostAddElement() {

        $data = [
            "type" => 0,
            "date" => date('Y-m-d'),
            "mileage" => 1000,
            "refill_full" => 1,
            "calc_refill_consumption" => 1,
            "refill_price" => 150,
            "refill_amount" => 50,
            "refill_total_price" => 75,
            "refill_location" => "Test Location",
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
            "type" => 0,
            "date" => date('Y-m-d'),
            "mileage" => 1500,
            "refill_full" => 0,
            "calc_refill_consumption" => 1,
            "refill_price" => 150,
            "refill_amount" => 50,
            "refill_total_price" => 75,
            "refill_location" => "Test Location",
            "notice" => "Test",
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

        $response = $this->request('GET', '/cars/' . $this->TEST_CAR_HASH . '/refuel/table/?' . http_build_query($data));

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

        $price = number_format($data["refill_price"], 2);
        $volume = number_format($data["refill_amount"], 2);
        $total_price = number_format($data["refill_total_price"], 2);
        $consumption = $data["refill_full"] == 1 ? number_format(5, 2) : "";

        $type = $data["refill_full"] == 1 ? "vollgetankt" : "nachgetankt";

        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["date"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["mileage"] ?? '') . '<\/td>\s*<td>' . preg_quote($price ?? '') . '<\/td>\s*<td>' . preg_quote($volume ?? '') . '<\/td>\s*<td>' . preg_quote($total_price ?? '') . '<\/td>\s*<td>' . preg_quote($type ?? '') . '<\/td>\s*<td>' . preg_quote($consumption ?? '') . '<\/td>\s*<td>' . preg_quote($data["refill_location"] ?? '') . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
