<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class NotificationsTest extends BaseTestCase {

    protected $uri_overview = "/notifications/";
    protected $uri_load_more = "/notifications/getNotifications";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<h2>Benachrichtigungen</h2>', $body);
    }

    public function testLoadMore() {

        $data = [
            "start" => 0,
            "count" => 10
        ];
        $response = $this->request('POST', $this->uri_load_more, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        
        $this->assertIsArray($json);
        
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
        
        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
        
        $this->assertArrayHasKey("count", $json);
        $this->assertIsInt($json["count"]);
        $this->assertSame($data["count"], count($json["data"]));
        
        $this->assertArrayHasKey("unseen", $json);
        $this->assertIsInt($json["unseen"]);
        
        $this->assertArrayHasKey("categories", $json);
        $this->assertIsArray($json["categories"]);
    }

}
