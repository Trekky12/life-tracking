<?php

namespace Tests\Functional\Trip\Event;

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
     * Add new Event
     */
    public function testGetChildEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_TRIP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Create the event
     * @depends testGetParentInList
     */
    public function testPostChildSave(array $tokens) {

        $data = [
            "name" => "Test",
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_TRIP_HASH), array_merge($data, $tokens[2]));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Delete event
     * @depends testGetParentInList
     */
    public function testDeleteChild(array $tokens) {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_TRIP_HASH) . $this->TEST_TRIP_EVENT_ID, $tokens[3]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

}
