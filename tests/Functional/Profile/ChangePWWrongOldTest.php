<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class ChangePWWrongOldTest extends BaseTestCase {

    protected $uri_overview = "/profile/changepassword";
    protected $uri_save = "/profile/changepassword";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/changepassword" method="POST">', $body);
    }

    /**
     */
    public function testChangePasswordWrongOld() {

        $data = [
            "oldpassword" => "user1",
            "newpassword1" => "user_new",
            "newpassword2" => "user_new"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Altes Passwort ist falsch", $body);
    }

}
