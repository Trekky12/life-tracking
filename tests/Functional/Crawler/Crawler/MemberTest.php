<?php

namespace Tests\Functional\Crawler\Crawler;

use Tests\Functional\Crawler\CrawlerTestBase;

class MemberTest extends CrawlerTestBase {

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * View 
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="crawlers_data_table"', $body);
    }

    /**
     * Edit
     */
    public function testGetParentCreatedEdit() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_CRAWLER_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     * 
     */
    public function testPostParentCreatedSave() {
        $data = [
            "id" => $this->TEST_CRAWLER_ID,
            "hash" => $this->TEST_CRAWLER_HASH,
            "name" => "Test Crawler 2 Updated",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_CRAWLER_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     * Delete
     */
    public function testDeleteParent() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CRAWLER_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
