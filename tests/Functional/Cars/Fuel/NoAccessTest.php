<?php

namespace Tests\Functional\Cars\Fuel;

use Tests\Functional\Base\BaseTestCase;

class NoAccessTest extends BaseTestCase {

    protected $uri_overview = "/cars/service/";
    protected $uri_edit = "/cars/service/edit/";
    protected $uri_save = "/cars/service/save/";
    protected $uri_delete = "/cars/service/delete/";
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

        return $this->extractJSCSRF($response);
    }

    /**
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave($csrf) {

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

        $response = $this->request('POST', $this->uri_save . $this->TEST_CAR, array_merge($data, $csrf));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_FUEL_ENTRY, $csrf);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
