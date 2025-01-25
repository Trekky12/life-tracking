<?php

namespace Tests\Functional\Home;

use Tests\Functional\Base\BaseTestCase;

class HomeTest extends BaseTestCase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }
    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetHomepageAfterLogin() {

        $response = $this->request('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
