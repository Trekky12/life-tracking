<?php

namespace Tests\Functional\Location;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class LocationSelfTest extends BaseTestCase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', '/location/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div id="mapid"></div>', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', '/location/edit/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="locationForm" action="/location/save/" method="POST">', $body);
    }



    public function testPostAddElement() {

        $data = [
            "gps_lat" => rand(0, 10),
            "gps_lng" => rand(0, 5),
            "gps_acc" => rand(0, 1000)
        ];

        $response = $this->request('POST', '/location/save/', $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/location/", $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostAddElement')]
    public function testAddedElement($location_data) {
        $response = $this->request('GET', '/location/markers');

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);

        $my_marker_id = -1;
        foreach ($json as $marker) {
            if ($marker["lat"] == $location_data["gps_lat"] && $marker["lng"] == $location_data["gps_lng"] && $marker["acc"] == $location_data["gps_acc"]) {
                $my_marker_id = $marker["id"];
            }
        }

        if ($my_marker_id == -1) {
            $this->fail("Marker not found!");
        }

        return $my_marker_id;
    }

    #[Depends('testAddedElement')]
    public function testDeleteElement($marker_id) {
        $response = $this->request('DELETE', '/location/delete/' . $marker_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }
}
