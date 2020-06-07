<?php

namespace Tests\Functional\Trip\Route;

use Tests\Functional\Trip\TripTestBase;

class MemberTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
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
                    "latLng" => ["lat" => 1,
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
            ]
        ];
        $response = $this->request('POST', $this->getURIRouteAdd($this->TEST_TRIP_HASH), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);

        return $data;
    }

    /**
     * Get the created route
     * @depends testPostRouteAdd
     */
    public function testGetCreatedRoute(array $data) {
        $response = $this->request('GET', $this->getURIRouteList($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        foreach ($json as $route) {
            if ($route["name"] == $data["name"] && $route["start_date"] == $data["start_date"] && $route["end_date"] == $data["end_date"]) {
                return intval($route["id"]);
            }
        }

        $this->fail("Route not found!");
    }

    /**
     * Get route waypoints
     * @depends testGetCreatedRoute
     */
    public function testGetRouteWaypoints(int $route_id) {
        $response = $this->request('GET', $this->getURIRouteWaypoints($this->TEST_TRIP_HASH) . "?route=" . $route_id);
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        $this->assertIsArray($json);
    }

    /**
     * Delete Route
     * @depends testGetCreatedRoute
     */
    public function testDeleteRoute(int $route_id) {
        $response = $this->request('DELETE', $this->getURIRouteDelete($this->TEST_TRIP_HASH) . $route_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
