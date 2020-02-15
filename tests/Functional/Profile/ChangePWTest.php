<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class ChangePWTest extends BaseTestCase {

    protected $uri_overview = "/profile/changepassword";
    protected $uri_save = "/profile/changepassword";

    public function testOverview() {
        $this->login("user", "user");

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/changepassword" method="POST">', $body);

        $this->logout();
    }

    /**
     * 
     */
    public function testChangePassword() {

        $this->login("user", "user");

        $data = [
            "oldpassword" => "user",
            "newpassword1" => "user_new",
            "newpassword2" => "user_new"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/", $response->getHeaderLine("Location"));

        $this->logout();

        return $data;
    }

    /**
     * @depends testChangePassword
     */
    public function testChangedPassword($data) {

        $this->login("user", $data["newpassword1"]);

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/changepassword" method="POST">', $body);

        $this->logout();
    }

    /**
     * @depends testChangedPassword
     */
    public function testChangePasswordBack() {

        $this->login("user", "user_new");

        $data = [
            "oldpassword" => "user_new",
            "newpassword1" => "user",
            "newpassword2" => "user"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/", $response->getHeaderLine("Location"));

        $this->logout();

        return $data;
    }

    /**
     * @depends testChangePasswordBack
     */
    public function testChangedPasswordBack($data) {

        $this->login("user", $data["newpassword1"]);

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/changepassword" method="POST">', $body);

        $this->logout();
    }

}
