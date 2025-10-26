<?php

namespace Tests\Functional\Cars\Fuel;

use Tests\Functional\Base\BaseTestCase;

class NoAccessTest extends BaseTestCase {

    protected $TEST_CAR_HASH = "ABCabc123";
    protected $TEST_FUEL_ENTRY = 1;

    protected $uri_child_overview = "/cars/HASH/refuel/";
    protected $uri_child_edit = "/cars/HASH/refuel/edit/";
    protected $uri_child_save = "/cars/HASH/refuel/save/";
    protected $uri_child_delete = "/cars/HASH/refuel/delete/";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CAR_HASH) . $this->TEST_FUEL_ENTRY);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    public function testPostAddElement() {

        $data = [
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
            "lat" => "10",
            "lng" => "5",
            "acc" => 30
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CAR_HASH), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_FUEL_ENTRY,
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
            "lat" => "10",
            "lng" => "5",
            "acc" => 30
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CAR_HASH) . $this->TEST_FUEL_ENTRY, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CAR_HASH) . $this->TEST_FUEL_ENTRY);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
