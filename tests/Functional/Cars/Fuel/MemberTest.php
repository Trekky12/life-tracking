<?php

namespace Tests\Functional\Cars\Fuel;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class MemberTest extends BaseTestCase {

    protected $uri_overview = "/cars/refuel/";
    protected $uri_edit = "/cars/refuel/edit/";
    protected $uri_save = "/cars/refuel/save/";
    protected $uri_delete = "/cars/refuel/delete/";
    protected $TEST_CAR = 1;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="fuel_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="gasolineForm" action="' . $this->uri_save . '" method="POST">', $body);
    }



    public function testPostAddElement() {

        $data = [
            "car" => $this->TEST_CAR,
            "type" => 0,
            "date" => date('Y-m-d'),
            "mileage" => 1000,
            "fuel_type" => 1,
            "fuel_calc_consumption" => 1,
            "fuel_price" => 150,
            "fuel_volume" => 50,
            "fuel_total_price" => 75,
            "fuel_location" => "Test Location",
            "notice" => "Test",
            "lat" => "10.00000000000000",
            "lng" => "5.00000000000000",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostAddElement')]
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data, "Test Car");

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

        $response = $this->request('GET', $this->uri_edit . $entry_id);

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
            "car" => $this->TEST_CAR,
            "type" => 0,
            "date" => date('Y-m-d'),
            "mileage" => 1500,
            "fuel_type" => 0,
            "fuel_calc_consumption" => 1,
            "fuel_price" => 150,
            "fuel_volume" => 50,
            "fuel_total_price" => 75,
            "fuel_location" => "Test Location",
            "notice" => "Test",
            "lat" => "10.00000000000000",
            "lng" => "5.00000000000000",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostElementCreatedSave')]
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data, "Test Car");

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    #[Depends('testGetElementUpdated')]
    #[Depends('testPostElementCreatedSave')]
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    #[Depends('testGetElementUpdated')]
    public function testDeleteElement(int $entry_id) {

        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data, $car_name = "") {

        $price = number_format($data["fuel_price"], 2);
        $volume = number_format($data["fuel_volume"], 2);
        $total_price = number_format($data["fuel_total_price"], 2);
        $consumption = $data["fuel_type"] == 1 ? number_format(5, 2) : "";

        $type = $data["fuel_type"] == 1 ? "vollgetankt" : "nachgetankt";

        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["date"] ?? '') . '<\/td>\s*<td>' . preg_quote($car_name ?? '') . '<\/td>\s*<td>' . preg_quote($data["mileage"] ?? '') . '<\/td>\s*<td>' . preg_quote($price ?? '') . '<\/td>\s*<td>' . preg_quote($volume ?? '') . '<\/td>\s*<td>' . preg_quote($total_price ?? '') . '<\/td>\s*<td>' . preg_quote($type ?? '') . '<\/td>\s*<td>' . preg_quote($consumption ?? '') . '<\/td>\s*<td>' . preg_quote($data["fuel_location"] ?? '') . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
