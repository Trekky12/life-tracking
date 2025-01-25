<?php

namespace Tests\Functional\Trip\Waypoint;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Trip\TripTestBase;

class NoAccessTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";
    protected $TEST_TRIP_EVENT_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Create the Waypoint
     */
    public function testPostWaypointAdd() {
        $data = [
            "start_date" => date('Y-m-d'),
            "start_lat" => 52.520007,
            "start_lng" => 13.404954,
            "end_date" => date('Y-m-d'),
            "type" => "WAYPOINT"
        ];
        $response = $this->request('POST', $this->getURIWaypointAdd($this->TEST_TRIP_HASH), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /** 
     * Delete Waypoint
     */
    #[Depends('testPostWaypointAdd')]
    public function testDeleteWaypoint() {
        $response = $this->request('DELETE', $this->getURIWaypointDelete($this->TEST_TRIP_HASH) . '?id=' . $this->TEST_TRIP_EVENT_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
