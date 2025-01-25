<?php

namespace Tests\Functional\Trip\Event;

use Tests\Functional\Trip\TripTestBase;

class NoAccessWithWrongParentTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";
    protected $TEST_TRIP_EVENT_ID = 2;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Access specific Event
     */
    public function testGetChildEditID() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_TRIP_HASH) . $this->TEST_TRIP_EVENT_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Update specific event
     */
    public function testPostChildSaveID() {

        $data = [
            "id" => $this->TEST_TRIP_EVENT_ID,
            "name" => "Test",
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_TRIP_HASH) . $this->TEST_TRIP_EVENT_ID, $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete event
     */
    public function testDeleteChild() {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_TRIP_HASH) . $this->TEST_TRIP_EVENT_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
