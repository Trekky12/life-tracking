<?php

namespace Tests\Functional\Admin\NotificationClients;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/notifications/clients/";
    protected $uri_test = "/notifications/clients/test/";
    protected $uri_delete = "/notifications/clients/delete/";
    protected $TEST_CLIENT_ID = 1;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    public function testTestNotification() {
        $response = $this->request('POST', $this->uri_test . $this->TEST_CLIENT_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDelete() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CLIENT_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

}
