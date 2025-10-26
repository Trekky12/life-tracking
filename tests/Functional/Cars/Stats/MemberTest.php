<?php

namespace Tests\Functional\Cars\Stats;

use Tests\Functional\Base\BaseTestCase;

class MemberTest extends BaseTestCase {

    protected $TEST_CAR_HASH = "ABCabc123";
    protected $uri_child_overview = "/cars/HASH/stats/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CAR_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="fuelChart"', $body);
        $this->assertStringContainsString('<h2>km/Jahr</h2>', $body);
    }
}
