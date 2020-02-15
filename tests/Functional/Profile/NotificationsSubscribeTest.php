<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class NotificationsSubscribeTest extends BaseTestCase {

    protected $uri_overview = "/notifications/clients/";
    protected $uri_subscribe = "/notifications/subscribe/";
    protected $TEST_ENDPOINT = "test_endpoint";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testSubscribe() {
        $data = [
            "endpoint" => $this->TEST_ENDPOINT,
            "publicKey" => "key",
            "authToken" => "token",
            "contentEncoding" => "encoding"
        ];
        $response = $this->request('POST', $this->uri_subscribe, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    public function testUpdateSubscription() {
        $data = [
            "endpoint" => $this->TEST_ENDPOINT,
            "publicKey" => "key_updated",
            "authToken" => "token_updated",
            "contentEncoding" => "encoding_updated"
        ];
        $response = $this->request('PUT', $this->uri_subscribe, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    public function testDeleteSubscription() {
        $data = [
            "endpoint" => $this->TEST_ENDPOINT
        ];
        $response = $this->request('DELETE', $this->uri_subscribe, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

}
