<?php

namespace Tests\Functional\Profile;

use PHPUnit\Framework\Attributes\Depends;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use Tests\Functional\Base\BaseTestCase;
use RobThree\Auth\TwoFactorAuth;

class TwoFactorAuthTest extends BaseTestCase {

    protected $uri_overview = "/profile/twofactorauth/";
    protected $uri_save = "/profile/twofactorauth/enable";
    protected $uri_save2 = "/profile/twofactorauth/disable";

    public function testOverview() {
        $this->login("user", "user");

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="' . $this->uri_save . '" method="POST">', $body);

        $matches = [];
        $re = '/<p class="twofactor-secret">(?<secret>.*)<\/p>/';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("secret", $matches);

        $secret = preg_replace('/\s+/', '', $matches["secret"]);

        $this->logout();

        return $secret;
    }

    #[Depends('testOverview')]
    public function testEnable($secret) {

        $this->login("user", "user");

        $tfa = new TwoFactorAuth(new EndroidQrCodeProvider());
        $code = $tfa->getCode($secret);

        $data = [
            "code" => $code
        ];

        $response = $this->request('POST', $this->uri_save, $data);


        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        $this->logout();

        return $secret;
    }

    #[Depends('testEnable')]
    public function testEnabled($secret) {
        $this->login("user", "user", $secret);

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="' . $this->uri_save2 . '" method="POST">', $body);

        $this->logout();

        return $secret;
    }

    #[Depends('testEnabled')]
    public function testDisable($secret) {

        $this->login("user", "user", $secret);

        $data = [
            "deactivate" => 1
        ];

        $response = $this->request('POST', $this->uri_save2, $data);


        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        $this->logout();
    }

    #[Depends('testDisable')]
    public function testDisabled() {
        $this->login("user", "user");

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="' . $this->uri_save . '" method="POST">', $body);

        $this->logout();
    }
}
