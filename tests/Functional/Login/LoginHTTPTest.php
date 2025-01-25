<?php

namespace Tests\Functional\Login;

use Tests\Functional\Base\BaseTestCase;

class LoginHTTPTest extends BaseTestCase {

    public function testGetHomepageHTTP() {
        $response = $this->request('GET', '/', [], ['user' => 'admin', 'pass' => 'admin']);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
    }

    public function testGetAPIHTTP() {
        $response = $this->request('GET', '/api', [], ['user' => 'admin', 'pass' => 'admin']);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<!DOCTYPE html>", $body);
    }
}
