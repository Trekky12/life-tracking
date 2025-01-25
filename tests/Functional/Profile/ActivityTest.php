<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class ActivityTest extends BaseTestCase {

    protected $uri_overview = "/profile/activity";
    protected $uri_load_more = "/profile/getActivities";

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
        $this->assertStringContainsString('<div id="activities">', $body);
    }



    public function testLoadMore() {

        $data = [
            "start" => 0,
            "count" => 20
        ];
        $response = $this->request('POST', $this->uri_load_more, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
        $this->assertSame($data["count"], count($json["data"]));

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);

        $this->assertArrayHasKey("count", $json);
        $this->assertIsInt($json["count"]);
    }
}
