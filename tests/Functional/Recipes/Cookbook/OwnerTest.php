<?php

namespace Tests\Functional\Recipes\Cookbook;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Recipes\RecipesCookbooksTestBase;

class OwnerTest extends RecipesCookbooksTestBase {

    protected $TEST_COOKBOOK_HASH = "ABCabc123";
    protected $TEST_RECIPE_HASH = "ABCabc123";

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
        $this->assertStringContainsString("recipes_cookbooks_table", $body);
    }

    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->uri_save . "\" method=\"POST\">", $body);
    }



    public function testPostParentSave() {
        $data = [
            "name" => "Test Cookbook",
            "users" => [1]
        ];
        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostParentSave')]
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
     * Edit project
     */
    #[Depends('testPostParentSave')]
    #[Depends('testGetParentCreated')]
    public function testGetParentCreatedEdit($data, array $result_data) {

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\" type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\" value=\"" . $data["name"] . "\">", $body);


        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">.*<input name="hash" type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        $this->assertArrayHasKey("hash", $matches);

        $result = [];
        $result["hash"] = $matches["hash"];
        $result["id"] = $matches["id"];

        $this->compareInputFields($body, $data);

        return $result;
    }

    #[Depends('testGetParentCreatedEdit')]
    public function testPostParentCreatedSave(array $result_data) {
        $data = [
            "id" => $result_data["id"],
            "hash" => $result_data["hash"],
            "name" => "Test Cookbook Updated",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $result_data["id"], $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testGetParentCreatedEdit')]
    #[Depends('testPostParentCreatedSave')]
    public function testChanges(array $result_data, $data) {
        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /** 
     * View
     */
    #[Depends('testGetParentCreated')]
    public function testGetViewParent(array $result_data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div id="recipes_list" data-cookbook=', $body);
    }

    /** 
     * View recipe of cookbook
     */
    public function testGetViewRecipe() {
        $response = $this->request('GET', $this->getURIChildView($this->TEST_COOKBOOK_HASH, $this->TEST_RECIPE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div class="recipe-description">', $body);
    }

    /** 
     * Delete / clean
     */
    #[Depends('testGetParentCreated')]
    public function testDeleteParent(array $result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
