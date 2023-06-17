<?php

namespace Tests\Functional\Cars\Fuel;

use Tests\Functional\Base\BaseTestCase;

class NoAccessTest extends BaseTestCase {

    protected $uri_overview = "/cars/refuel/";
    protected $uri_edit = "/cars/refuel/edit/";
    protected $uri_save = "/cars/refuel/save/";
    protected $uri_delete = "/cars/refuel/delete/";
    protected $TEST_CAR = 1;
    protected $TEST_FUEL_ENTRY = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $matches = [];
        $re = '/<tbody>\s*<\/tbody>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        $this->assertFalse(empty($matches));
    }

    /**
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_FUEL_ENTRY);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * 
     */
    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_FUEL_ENTRY,
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

        $response = $this->request('POST', $this->uri_save . $this->TEST_CAR, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_FUEL_ENTRY);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
