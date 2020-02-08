<?php

namespace Tests\Functional\Crawler\Link;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected $uri_child_overview = "/crawlers/HASH/links/";
    protected $uri_child_edit = "/crawlers/HASH/links/edit/";
    protected $uri_child_save = "/crawlers/HASH/links/save/";
    protected $uri_child_delete = "/crawlers/HASH/links/delete/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="crawlers_links_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="crawlerLinksForm" action="' . $this->getURIChildSave($this->TEST_CRAWLER_HASH) . '" method="POST">', $body);

        return $this->extractFormCSRF($response);
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

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_CRAWLER_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data, $this->TEST_CRAWLER_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Edit created element
     * @depends testAddedElement
     */
    public function testGetElementCreatedEdit(array $result_data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH) . $result_data["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $result_data["id"] . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"crawlerLinksForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $result = [];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(array $result_data) {

        $data = [
            "id" => $result_data["id"],
            "name" => "Test Category 2 Updated",
            "link" => "http://localhost/1",
            "parent" => null,
            "position" => 2
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_CRAWLER_HASH) . $result_data["id"], array_merge($data, $result_data["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_CRAWLER_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data, $this->TEST_CRAWLER_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement($result_data) {

        $response1 = $this->request('GET', $this->getURIChildOverview($this->TEST_CRAWLER_HASH));
        $csrf = $this->extractJSCSRF($response1);


        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_CRAWLER_HASH) . $result_data["id"], $csrf);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data, $hash) {
        $parent_link = "";

        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"]) . '<\/td>\s*<td>' . str_replace('/', "\/", $data["link"]) . '<\/td>\s*<td>' . str_replace('/', "\/", $parent_link) . '<\/td>\s*<td>' . preg_quote($data["position"]) . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);
        return $matches;
    }

    protected function getURIChildOverview($hash) {
        return str_replace("HASH", $hash, $this->uri_child_overview);
    }

}
