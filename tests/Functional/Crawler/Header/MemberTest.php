<?php

namespace Tests\Functional\Crawler\Header;

use Tests\Functional\Crawler\CrawlerTestBase;

class MemberTest extends CrawlerTestBase {

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

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * @depends testGetAddElement
     */
    public function testPostAddElement($csrf_data) {

        $data = [
            "headline" => "Test Header",
            "field_name" => "title",
            "field_link" => "link",
            "field_content" => "",
            "position" => 1,
            "diff" => 0,
            "sortable" => 0,
            "prefix" => "pre",
            "suffix" => "suff",
            "sort" => "asc",
            "datatype" => "CHAR"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH), array_merge($data, $csrf_data));

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);

    }

    /**
     */
    public function testPostElementCreatedSave() {
        
        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);
        
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

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_HEADER_ID, array_merge($data, $csrf));

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     */
    public function testDeleteElement() {

        $response1 = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));
        $csrf = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_HEADER_ID, $csrf);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    protected function getURIChildOverview($hash) {
        return str_replace("HASH", $hash, $this->uri_child_overview);
    }

}
