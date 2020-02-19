<?php

namespace Tests\Functional\Cars\Service;

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
        $this->assertStringContainsString('<table id="service_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="gasolineForm" action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "car" => $this->TEST_CAR,
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

        $response = $this->request('POST', $this->uri_save, $data);

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

        return intval($row["id_edit"]);
    }

    /**
     * Edit created element
     * @depends testAddedElement
     * @depends testPostAddElement
     */
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

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "car" => $this->TEST_CAR,
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

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

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

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetElementUpdated
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {

        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data, $car_name = "") {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["date"]) . '<\/td>\s*<td>' . preg_quote($car_name) . '<\/td>\s*<td>' . preg_quote($data["mileage"]) . '<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td>x<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
