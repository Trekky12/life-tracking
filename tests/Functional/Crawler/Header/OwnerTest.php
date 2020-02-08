<?php

namespace Tests\Functional\Crawler\Header;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected $uri_child_overview = "/crawlers/HASH/headers/";
    protected $uri_child_edit = "/crawlers/HASH/headers/edit/";
    protected $uri_child_save = "/crawlers/HASH/headers/save/";
    protected $uri_child_delete = "/crawlers/HASH/headers/delete/";

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
        $this->assertStringContainsString('<table id="crawlers_headers_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="crawlerHeadersForm" action="' . $this->getURIChildSave($this->TEST_CRAWLER_HASH) . '" method="POST">', $body);

        return $this->extractFormCSRF($response);
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
        $re = '/<form class="form-horizontal" id=\"crawlerHeadersForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
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
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["headline"]) . '<\/td>\s*<td>' . preg_quote($data["field_name"]) . '<\/td>\s*<td>' . str_replace('/', "\/", $data["field_link"]) . '<\/td>\s*<td>' . preg_quote($data["field_content"]) . '<\/td>\s*<td>' . preg_quote($data["position"]) . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getURIChildOverview($hash) {
        return str_replace("HASH", $hash, $this->uri_child_overview);
    }

}
