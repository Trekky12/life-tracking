<?php

namespace Tests\Functional\Cars\Fuel;

use Tests\Functional\Base\BaseTestCase;

class MemberTest extends BaseTestCase {

    protected $uri_overview = "/cars/service/";
    protected $uri_edit = "/cars/service/edit/";
    protected $uri_save = "/cars/service/save/";
    protected $uri_delete = "/cars/service/delete/";
    
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

        return $this->extractFormCSRF($response);
    }

    /**
     * @depends testGetAddElement
     */
    public function testPostAddElement($csrf_data) {

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
            "lat" => "10",
            "lng" => "5",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data, "Test Car");

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Edit created element
     * @depends testAddedElement
     */
    public function testGetElementCreatedEdit(array $result_data) {

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $result_data["id"] . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"gasolineForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $result = [];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(array $result_data) {

        $data = [
            "id" => $result_data["id"],
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
            "lat" => "10",
            "lng" => "5",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data, "Test Car");

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement($result_data) {

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);


        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"], $csrf);

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
        $re = '/<tr>\s*<td>' . preg_quote($data["date"]) . '<\/td>\s*<td>' . preg_quote($car_name) . '<\/td>\s*<td>' . preg_quote($data["mileage"]) . '<\/td>\s*<td>' . preg_quote($price) . '<\/td>\s*<td>' . preg_quote($volume) . '<\/td>\s*<td>' . preg_quote($total_price) . '<\/td>\s*<td>' . preg_quote($type) . '<\/td>\s*<td>' . preg_quote($consumption) . '<\/td>\s*<td>' . preg_quote($data["fuel_location"]) . '<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
