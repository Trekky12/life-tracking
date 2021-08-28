<?php

namespace Tests\Functional\Recipes\Recipe;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/recipes/";
    protected $uri_edit = "/recipes/edit/";
    protected $uri_save = "/recipes/save/";
    protected $uri_delete = "/recipes/delete/";
    
    protected $uri_view = "/recipes/HASH/view";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div id="recipes_list"></div>', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST"', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Recipe",
            "description" => "Description",
            "preparation_time" => 10,
            "waiting_time" => 20,
            "servings" => 4,
            "link" => "#",
            "steps" => [
                0 => [
                    "name" => "Test Step 1",
                    "preparation_time" => '1',
                    "waiting_time" => '2',
                    "ingredients" => [
                        0 => [
                            "amount" => '1',
                            "ingredient" => 1,
                            "notice" => "Test notice"
                        ]
                    ],
                    "description" => "Test Step 1 Notice"
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());

        $redirect = $response->getHeaderLine("Location");
        $matches = [];
        $re = '/\/recipes\/(?<hash>[a-zA-Z0-9]*)\/view/';
        preg_match($re, $redirect, $matches);

        $this->assertArrayHasKey("hash", $matches);

        return ["data" => $data, "hash" => $matches["hash"]];
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($result) {
        $response = $this->request('GET', str_replace("HASH", $result["hash"], $this->uri_view));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getIDFromView($body);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Edit created element
     * @depends testAddedElement
     * @depends testPostAddElement
     */
    public function testGetElementCreatedEdit(int $entry_id, array $result) {

        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST" enctype="multipart\/form-data">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $this->compareInputFields($body, $result["data"]);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "name" => "Test Recipe Updated",
            "description" => "Description Updated",
            "preparation_time" => 11,
            "waiting_time" => 21,
            "servings" => 5,
            "link" => "#",
            "steps" => [
                0 => [
                    "name" => "Test Step 2",
                    "preparation_time" => '2',
                    "waiting_time" => '3',
                    "ingredients" => [
                        0 => [
                            "amount" => '2',
                            "ingredient" => 2,
                            "notice" => "Test notice 2"
                        ]
                    ],
                    "description" => "Test Step 2 Notice"
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertMatchesRegularExpression('/\/recipes\/(?<hash>[a-zA-Z0-9]*)\/view/', $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testGetElementCreatedEdit
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $entry_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
        
        return $entry_id;
    }

    /**
     * @depends testChanges
     */
    public function testDeleteElement(int $entry_id) {
        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":"","redirect":"\/recipes\/"}', $body);
    }

    protected function getIDFromView($body) {
        $matches = [];
        $re = '/<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)"><button class="white">.*?<\/button><\/a>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete"><button class="white">.*?<\/button><\/a>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
