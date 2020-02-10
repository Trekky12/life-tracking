<?php

namespace Tests\Functional\Splitbill\Group;

use Tests\Functional\Splitbill\SplitbillTestBase;

class OwnerTest extends SplitbillTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testRedirect() {
        $response = $this->request('GET', '/splitbills/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testGetOverview() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("splitbills_groups_table", $body);
    }

    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->uri_save . "\" method=\"POST\">", $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * @depends testGetParentEdit
     */
    public function testPostParentSave(array $csrf_data) {
        $data = ["name" => "Testgroup", "users" => [1]];
        $response = $this->request('POST', $this->uri_save, array_merge($data, $csrf_data));

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
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Edit group
     * @depends testPostParentSave
     * @depends testGetParentCreated
     */
    public function testGetParentCreatedEdit($data, array $result_data) {

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\"  type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\" value=\"" . $data["name"] . "\">", $body);


        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">.*<input name="hash"  type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        $this->assertArrayHasKey("hash", $matches);

        $result = [];
        $result["hash"] = $matches["hash"];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetParentCreatedEdit
     */
    public function testPostParentCreatedSave(array $result_data) {
        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testgroup Updated", "users" => [1,3]];
        $response = $this->request('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    /**
     * View Group
     * @depends testGetParentCreated
     */
    public function testGetViewParent(array $result_data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("splitbills_bills_table", $body);
    }

    /**
     * Delete / clean
     * @depends testGetParentCreated
     */
    public function testDeleteParent(array $result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}