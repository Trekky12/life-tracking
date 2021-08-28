<?php

namespace Tests\Functional\Admin\NotificationClients;

use Tests\Functional\Base\BaseTestCase;

class AdminTest extends BaseTestCase {

    protected $uri_overview = "/notifications/clients/";
    protected $uri_subscribe = "/notifications/subscribe/";
    protected $uri_test = "/notifications/clients/test/";
    protected $uri_delete = "/notifications/clients/delete/";
    protected $TEST_ENDPOINT = "test_endpoint";

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
        $this->assertStringContainsString('<table id="notifications_table"', $body);

        $data = [
            "user" => "admin",
            "user_agent" => $this->USE_GUZZLE ? $this->USER_AGENT : "",
            "ip" => $this->USE_GUZZLE ? $this->LOCAL_IP : ""
        ];

        $rows = $this->getElementsInTable($body, $data);

        $this->assertTrue(empty($rows));
    }

    public function testSubscribe() {

        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

        $data = [
            "endpoint" => $this->TEST_ENDPOINT,
            "publicKey" => $keys["publicKey"],
            "authToken" => base64_encode("token"),
            "contentEncoding" => "aes128gcm"
        ];


        $response = $this->request('POST', $this->uri_subscribe, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        
        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    /**
     * @depends testSubscribe
     */
    public function testSubscribed() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $data = [
            "user" => "admin",
            "user_agent" => $this->USE_GUZZLE ? $this->USER_AGENT : "",
            "ip" => $this->USE_GUZZLE ? $this->LOCAL_IP : ""
        ];
        
        $rows = $this->getElementsInTable($body, $data);

        $this->assertFalse(empty($rows));
        $this->assertCount(1, $rows);

        return intval($rows[0]["id_delete"]);
    }

    /**
     * @depends testSubscribed
     */
    public function testTestNotification(int $client_id) {        
        $response = $this->request('POST', $this->uri_test . $client_id);
        
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    /**
     * @depends testSubscribed
     */
    public function testDelete(int $client_id) {
        $response = $this->request('DELETE', $this->uri_delete . $client_id);
    
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * @depends testDelete
     */
    public function testDeleted() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $data = [
            "user" => "admin",
            "user_agent" => $this->USE_GUZZLE ? $this->USER_AGENT : "",
            "ip" => $this->USE_GUZZLE ? "127.0.0.1" : ""
        ];

        $rows = $this->getElementsInTable($body, $data);

        $this->assertTrue(empty($rows));
    }

    protected function getElementsInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["user"]) . '<\/td>\s*<td>' . preg_quote($data["ip"]) . '<\/td>\s*<td>' . preg_quote($data["user_agent"]) . '<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_test) . '(?<id_edit>[0-9]*)">.*?<\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

}
