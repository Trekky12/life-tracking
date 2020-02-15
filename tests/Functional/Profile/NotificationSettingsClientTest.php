<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class NotificationSettingsClientTest extends BaseTestCase {

    protected $uri_overview = "/notifications/manage/";
    protected $uri_set_category_client = "/notifications/setCategorySubscription";
    protected $uri_get_categories_client = "/notifications/getCategories";
    protected $TEST_ENDPOINT = "endpoint";
    protected $TEST_CLIENT_CATEGORY = 1;

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
        $this->assertStringContainsString('<h2>Push Benachrichtigungen</h2>', $body);
    }

    public function testSetClientCategory() {

        $data = [
            "endpoint" => $this->TEST_ENDPOINT,
            "category" => $this->TEST_CLIENT_CATEGORY,
            "type" => 1
        ];
        $response = $this->request('POST', $this->uri_set_category_client, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    public function testGetClientCategories() {

        $data = [
            "endpoint" => $this->TEST_ENDPOINT
        ];
        $response = $this->request('POST', $this->uri_get_categories_client, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);

        // data contains an array with all selected categories!
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
        $this->assertContains($this->TEST_CLIENT_CATEGORY, $json["data"]);
    }

    public function testSetClientCategoryBack() {

        $data = [
            "endpoint" => $this->TEST_ENDPOINT,
            "category" => $this->TEST_CLIENT_CATEGORY,
            "type" => 0
        ];
        $response = $this->request('POST', $this->uri_set_category_client, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }
    
    public function testGetClientCategoriesBack() {

        $data = [
            "endpoint" => $this->TEST_ENDPOINT
        ];
        $response = $this->request('POST', $this->uri_get_categories_client, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);

        // data contains an array with all selected categories!
        $this->assertArrayHasKey("data", $json);
        $this->assertIsArray($json["data"]);
        $this->assertNotContains($this->TEST_CLIENT_CATEGORY, $json["data"]);
    }

}
