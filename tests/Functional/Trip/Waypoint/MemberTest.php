<?php

namespace Tests\Functional\Trip\Waypoint;

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

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);

        $this->assertArrayHasKey("id", $json);
        $this->assertIsNumeric($json["id"]);

        return intval($json["id"]);
    }

    /**
     * Delete Waypoint
     * @depends testPostWaypointAdd
     */
    public function testDeleteWaypoint(int $waypoint_id) {
        $response = $this->request('DELETE', $this->getURIWaypointDelete($this->TEST_TRIP_HASH) . '?id=' . $waypoint_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
