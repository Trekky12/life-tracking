<?php

namespace Tests\Functional\Cars;

use Tests\Functional\Base\BaseTestCase;

class StatsTest extends BaseTestCase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', '/cars/stats/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="fuelChart"', $body);
        $this->assertStringContainsString('<h2>km/Jahr</h2>', $body);
    }
}
