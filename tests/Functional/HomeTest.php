<?php

namespace Tests\Functional;

class HomeTest extends BaseTestCase {


    protected function setUp(): void {
        $this->login("admin", "admin");
    }
    protected function tearDown(): void{
        $this->logout();
    }


    public function testGetHomepageAfterLogin1() {

        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());

    }

}
