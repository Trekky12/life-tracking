<?php

namespace Tests\Functional\Trip\Route;

use Tests\Functional\Trip\TripTestBase;

class NoAccessTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";
    protected $TEST_ROUTE_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Create the Route
     */
    public function testPostRouteAdd() {
        $data = [
            "name" => "Test Route",
            "start_date" => date('Y-m-d'),
            "end_date" => date('Y-m-d'),
            "waypoints" => [
                [
                    "latLng" => [
                        "lat" => 1,
                        "lng" => 2
                    ],
                    "name" => "Waypoint 1"
                ],
                [
                    "latLng" => [
                        "lat" => 3,
                        "lng" => 4
                    ],
                    "name" => "Waypoint 2"
                ]
            ],
            "profile" => "driving"
        ];
        $response = $this->request('POST', $this->getURIRouteAdd($this->TEST_TRIP_HASH), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);

        return $data;
    }

    /** 
     * Get the created route
     */
    public function testGetCreatedRoute() {
        $response = $this->request('GET', $this->getURIRouteList($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /** 
     * Get route waypoints
     */
    public function testGetRouteWaypoints() {
        $response = $this->request('GET', $this->getURIRouteWaypoints($this->TEST_TRIP_HASH) . "?route=" . $this->TEST_ROUTE_ID);
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /** 
     * Delete Route
     */
    public function testDeleteRoute() {
        $response = $this->request('DELETE', $this->getURIRouteDelete($this->TEST_TRIP_HASH) . $this->TEST_ROUTE_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
