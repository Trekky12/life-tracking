<?php

namespace Tests\Functional\Crawler\Crawler;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetOverview() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("crawlers_table", $body);
    }

    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="crawlerForm" action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostParentSave() {
        $data = [
            "name" => "Test Crawler 2",
            "filter" => "changedOn",
            "users" => [1]
        ];
        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostParentSave
     */
    public function testGetParentCreated($data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getParent($body, $data["name"]);

        $this->assertArrayHasKey("hash", $row);
        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["hash"] = $row["hash"];
        $result["id"] = $row["id_edit"];

        return $result;
    }

    /**
     * Edit
     * @depends testPostParentSave
     * @depends testGetParentCreated
     */
    public function testGetParentCreatedEdit($data, array $result_data) {

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\" type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\" value=\"" . $data["name"] . "\">", $body);


        $matches = [];
        $re = '/<form class="form-horizontal" id="crawlerForm" action="(?<save>[\/a-z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">.*<input name="hash" type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        $this->assertArrayHasKey("hash", $matches);
        
        $this->compareInputFields($body, $data);

        $result = [];
        $result["hash"] = $matches["hash"];
        $result["id"] = $matches["id"];

        return $result;
    }

    /**
     * 
     * @depends testGetParentCreatedEdit
     */
    public function testPostParentCreatedSave(array $result_data) {
        $data = [
            "id" => $result_data["id"],
            "hash" => $result_data["hash"],
            "name" => "Test Crawler 2 Updated",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $result_data["id"], $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
        
        return $data;
    }

    /**
     * View 
     * @depends testGetParentCreated
     */
    public function testGetViewParent(array $result_data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="crawlers_data_table"', $body);
    }
    
    /**
     * @depends testGetParentCreatedEdit
     * @depends testPostParentCreatedSave
     */
    public function testChanges(array $result_data, array $data) {
        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * Delete
     * @depends testGetParentCreated
     */
    public function testDeleteParent(array $result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
