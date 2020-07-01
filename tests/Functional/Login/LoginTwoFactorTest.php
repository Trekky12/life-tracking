<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;
use RobThree\Auth\TwoFactorAuth;

class LoginTwoFactorTest extends BaseTestCase {

    protected $uri_login = "/login";
    
    private $SECRET = "ZONTUSYMICAFZZBMDZQXGSCXWSEPTKGW";

    public function testLoginWithoutSecret() {
        $response1 = $this->request('GET', '/login');
        $csrf_token = $this->extractFormCSRF($response1);
        
        $data = [
            "username" => "user2fa",
            "password" => "user2fa"
        ];
        $response2 = $this->request('POST', '/login', array_merge($data, $csrf_token));

        $this->assertEquals(302, $response2->getStatusCode());
        $this->assertEquals("/login", $response2->getHeaderLine("Location"));
    }
    
    
    public function testLoginWitSecret() {
        $response1 = $this->request('GET', '/login');
        $csrf_token = $this->extractFormCSRF($response1);
        
        $tfa = new TwoFactorAuth();
        $code = $tfa->getCode($this->SECRET);
                
        $data = [
            "username" => "user2fa",
            "password" => "user2fa",
            "code" => $code
        ];
        $response2 = $this->request('POST', '/login', array_merge($data, $csrf_token));

        $this->assertEquals(301, $response2->getStatusCode());
        $this->assertEquals("/", $response2->getHeaderLine("Location"));
        
        $this->logout();
    }

    
}
