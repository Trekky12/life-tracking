<?php

namespace Tests\Functional\Location;

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

        return $this->extractFormCSRF($response);
    }

    /**
     * @depends testGetAddElement
     */
    public function testPostAddElement($csrf_data) {

        $data = ["gps_lat" => "10", "gps_lng" => "5", "gps_acc" => 30];

        $response = $this->request('POST', '/location/save/', array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/location/", $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
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

    /**
     * @depends testAddedElement
     */
    public function testDeleteElement($marker_id) {
        
        $response1 = $this->request('GET', '/location/');
        $csrf = $this->extractJSCSRF($response1);
        
        
        $response = $this->request('DELETE', '/location/delete/'.$marker_id, $csrf);
        
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

}
