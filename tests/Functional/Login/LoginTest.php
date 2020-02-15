<?php

namespace Tests\Functional\Login;

use Tests\Functional\Base\BaseTestCase;

class LoginTest extends BaseTestCase {

    /**
     * 
     */
    public function testGetHomepageWithoutLogin() {
        $response = $this->request('GET', '/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
    }

    /**
     * Test Login
     */
    public function testLoginCSRFFail() {
        $response = $this->request('POST', '/login', array("username" => "admin", "password" => "admin"));
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Failed CSRF check!', (string) $response->getBody());
    }

    public function testLoginPage() {
        $response = $this->request('GET', '/login');

        $this->assertEquals(200, $response->getStatusCode());

        return $this->extractFormCSRF($response);
    }

    /**
     * Test Login
     * @depends testLoginPage
     */
    public function testLogin(array $csrf_data) {
        $data = [
            "username" => "admin",
            "password" => "admin"
        ];
        $response = $this->request('POST', '/login', array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/", $response->getHeaderLine("Location"));
    }

    /**
     * @depends testLogin
     */
    public function testGetHomepageAfterLogin() {

        $response = $this->request('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @depends testLogin
     */
    public function testLogout() {

        $response = $this->request('GET', '/logout');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
        $this->assertStringContainsString("token=;", $response->getHeaderLine("Set-Cookie"));
    }

}
