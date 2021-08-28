<?php

namespace Tests\Functional\Admin\LoginTokens;

use Tests\Functional\Base\BaseTestCase;

class AdminTest extends BaseTestCase {

    protected $uri_overview = "/users/tokens/";

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
        $this->assertStringContainsString('<table id="tokens_table"', $body);

        // check for current logged in user
        $rows = $this->getElementInTable($body);
        $this->assertCount(1, $rows);
    }

    protected function getElementInTable($body) {
        $matches = [];
        $re = '/<tr>\s*<td>x<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>(.?)*<\/td>\s*<td>(.?)*<\/td>\s*<td>' . $this->LOCAL_IP . '<\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[a-z\/0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
