<?php

namespace Tests\Functional\Trip\Trip;

use Tests\Functional\Trip\TripTestBase;

class OwnerNoViewTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "DEFdef456";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * View Project (owner -> has no access to view)
     */
    public function testGetViewParentOwner() {
        $response = $this->request('GET', $this->getURIView($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
