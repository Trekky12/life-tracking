<?php

namespace Tests\Functional\Timesheet\Project;

use Tests\Functional\Timesheet\TimesheetTestBase;

class OwnerNoViewTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "DEFdef456";

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
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }
}
