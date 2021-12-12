<?php

namespace Tests\Functional\Crawler\Header;

use Tests\Functional\Crawler\CrawlerTestBase;

class NoAccessWithWrongParentTest extends CrawlerTestBase {

    protected $TEST_CRAWLER_HASH = "DEFdef456";

    protected $uri_child_overview = "/crawlers/HASH/headers/";
    protected $uri_child_edit = "/crawlers/HASH/headers/edit/";
    protected $uri_child_save = "/crawlers/HASH/headers/save/";
    protected $uri_child_delete = "/crawlers/HASH/headers/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetAddElementID() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH).$this->TEST_CRAWLER_HEADER_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


    /**
     */
    public function testPostElementCreatedSave() {
 
        $data = [
            "id" => $this->TEST_CRAWLER_HEADER_ID,
            "headline" => "Test Header Updated",
            "field_name" => "title2",
            "field_link" => "link2",
            "field_content" => "content",
            "position" => 2,
            "diff" => 1,
            "sortable" => 1,
            "prefix" => "pre1",
            "suffix" => "suff1",
            "sort" => "desc",
            "datatype" => "DECIMAL"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_HEADER_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     */
    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_HEADER_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
