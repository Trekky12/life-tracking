<?php

namespace Tests\Functional\Workout\Plan;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/plans/";
    protected $uri_edit = "/workouts/plans/edit/";
    protected $uri_save = "/workouts/plans/save/";
    protected $uri_delete = "/workouts/plans/delete/";

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
        $this->assertStringContainsString('<table id="workouts_plans_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Workout Plan 1"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Edit created element
     * @depends testAddedElement
     * @depends testPostAddElement
     */
    public function testGetElementCreatedEdit(int $entry_id, array $data) {

        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        
        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "name" => "Test Workout Plan 1 Updated"
        ];

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetElementCreatedEdit
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $entry_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {
        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"]) . '<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
