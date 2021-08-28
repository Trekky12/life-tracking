<?php

namespace Tests\Functional\Admin\Modules;

use Tests\Functional\Base\BaseTestCase;

class BoardsTest extends BaseTestCase {

    protected function setUp(): void {
        $this->login("user_module_boards", "user_module_boards");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testMain() {
        $response = $this->request('GET', "/");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testModuleLocation() {
        $response = $this->request('GET', "/location/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleFinances() {
        $response = $this->request('GET', "/finances/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleCars() {
        $response = $this->request('GET', "/cars/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleBoards() {
        $response = $this->request('GET', "/boards/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringNotContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleCrawlers() {
        $response = $this->request('GET', "/crawlers/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleSplitbills() {
        $response = $this->request('GET', "/splitbills/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleTrips() {
        $response = $this->request('GET', "/trips/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleTimesheets() {
        $response = $this->request('GET', "/timesheets/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleWorkouts() {
        $response = $this->request('GET', "/workouts/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testModuleRecipes() {
        $response = $this->request('GET', "/recipes/");

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

}
