<?php

namespace Tests\Functional\Admin\Banlist;

use Tests\Functional\Base\BaseTestCase;

class AdminTest extends BaseTestCase {

    protected $uri_overview = "/banlist/";
    protected $uri_delete = "/banlist/deleteIP/";

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
        $this->assertStringContainsString('<table id="banlist_table"', $body);

        $data = [
            "ip" => "127.0.0.2",
            "user" => "user2"
        ];

        $rows = $this->getElementsInTable($body, $data);
        $this->assertFalse(empty($rows));
    }

    protected function getElementsInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>' . preg_quote($data["ip"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["user"] ?? '') . '<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<ip_delete>.*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
