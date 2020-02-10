<?php

namespace Tests\Functional\Admin\Users;

use Tests\Functional\Base\BaseTestCase;

class ForcePWChange extends BaseTestCase {

    protected $uri_overview = "/profile/changepassword";

    protected function setUp(): void {
        $this->login("user_force_pw_change", "user_force_pw_change");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testMain() {
        $response = $this->request('GET', "/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }
    
    public function testProfile() {
        $response = $this->request('GET', "/profile/activity");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }
    
    public function testNotifications() {
        $response = $this->request('GET', "/notifications/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleLocation() {
        $response = $this->request('GET', "/location/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleFinances() {
        $response = $this->request('GET', "/finances/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleCars() {
        $response = $this->request('GET', "/cars/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleBoards() {
        $response = $this->request('GET', "/boards/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleCrawlers() {
        $response = $this->request('GET', "/crawlers/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleSplitbills() {
        $response = $this->request('GET', "/splitbills/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleTrips() {
        $response = $this->request('GET', "/trips/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testModuleTimesheets() {
        $response = $this->request('GET', "/timesheets/");

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

}
