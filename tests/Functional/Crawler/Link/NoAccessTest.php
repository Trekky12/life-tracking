<?php

namespace Tests\Functional\Crawler\Link;

use Tests\Functional\Crawler\CrawlerTestBase;

class NoAccessTest extends CrawlerTestBase {

    protected $uri_child_overview = "/crawlers/HASH/links/";
    protected $uri_child_edit = "/crawlers/HASH/links/edit/";
    protected $uri_child_save = "/crawlers/HASH/links/save/";
    protected $uri_child_delete = "/crawlers/HASH/links/delete/";

    protected function setUp(): void {
        $this->login("user2", "user2");
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
            "name" => "Test Category 2",
            "link" => "http://localhost",
            "parent" => null,
            "position" => 1
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
            "id" => $this->TEST_CRAWLER_LINK_ID,
            "name" => "Test Category 2 Updated",
            "link" => "http://localhost/1",
            "parent" => null,
            "position" => 2
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_LINK_ID, array_merge($data, $csrf));

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     */
    public function testDeleteElement() {

        $response1 = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));
        $csrf = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CRAWLER_HASH) . $this->TEST_CRAWLER_LINK_ID, $csrf);

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
