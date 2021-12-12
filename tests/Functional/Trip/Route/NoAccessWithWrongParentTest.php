<?php

namespace Tests\Functional\Trip\Route;

use Tests\Functional\Trip\TripTestBase;

class NoAccessWithWrongParentTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";
    protected $TEST_ROUTE_ID = 2;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }
    
    /**
     * Get route waypoints
     * @de1pends testGetCreatedRoute
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
