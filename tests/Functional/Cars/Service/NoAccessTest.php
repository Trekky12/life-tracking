<?php

namespace Tests\Functional\Cars\Service;

use Tests\Functional\Base\BaseTestCase;

class NoAccessTest extends BaseTestCase {

    protected $uri_overview = "/cars/service/";
    protected $uri_edit = "/cars/service/edit/";
    protected $uri_save = "/cars/service/save/";
    protected $uri_delete = "/cars/service/delete/";
    protected $TEST_CAR = 1;
    protected $TEST_SERVICE_ENTRY = 2;

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
        $response = $this->request('GET', $this->uri_edit . $this->TEST_SERVICE_ENTRY);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }



    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_SERVICE_ENTRY,
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
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_SERVICE_ENTRY);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
