<?php

namespace Tests\Functional\Crawler\Link;

use Tests\Functional\Crawler\CrawlerTestBase;

class NoAccessWithWrongParentTest extends CrawlerTestBase {

    protected $TEST_CRAWLER_HASH = "DEFdef456";

    protected $uri_child_overview = "/crawlers/HASH/links/";
    protected $uri_child_edit = "/crawlers/HASH/links/edit/";
    protected $uri_child_save = "/crawlers/HASH/links/save/";
    protected $uri_child_delete = "/crawlers/HASH/links/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }


    public function testGetAddElementID() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_LINK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }



    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_CRAWLER_LINK_ID,
            "name" => "Test Category 2 Updated",
            "link" => "http://localhost/1",
            "parent" => null,
            "position" => 2
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_LINK_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }



    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_LINK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
