<?php

namespace Tests\Functional;

class LoginTest extends BaseTestCase {

    /**
     * 
     */
    public function testGetHomepageWithoutLogin() {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
    }

    /**
     * Test Login
     */
    public function testLoginCSRFFail() {
        $response = $this->runApp('POST', '/login', array("username" => "admin", "password" => "admin"));
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Failed CSRF check!', (string) $response->getBody());
    }

    
    public function testLoginPage() {
        $response = $this->getLoginPage();

        $this->assertEquals(200, $response->getStatusCode());

        return $this->extractCSRF($response);
    }

    /**
     * Test Login
     * @depends testLoginPage
     */
    public function testLogin(array $csrf_data) {
        $response = $this->postLoginPage($csrf_data, "admin", "admin");

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/", $response->getHeaderLine("Location"));
    }

    /**
     * @depends testLogin
     */
    public function testGetHomepageAfterLogin() {

        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @depends testLogin
     */
    public function testLogout() {

        $response = $this->getLogout();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
        $this->assertStringContainsString("token=;", $response->getHeaderLine("Set-Cookie"));
    }
}
