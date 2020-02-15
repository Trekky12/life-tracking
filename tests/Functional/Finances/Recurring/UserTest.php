<?php

namespace Tests\Functional\Finances\Recurring;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/finances/recurring/";
    protected $uri_edit = "/finances/recurring/edit/";
    protected $uri_save = "/finances/recurring/save/";
    protected $uri_delete = "/finances/recurring/delete/";

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
        $this->assertStringContainsString('<table id="recurring_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" id="financesRecurringForm" action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "type" => 0,
            "category" => null,
            "description" => "Test",
            "value" => rand(0, 10000) / 100,
            "paymethod" => "",
            "common" => 0,
            "common_value" => "",
            "notice" => "testing",
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 1
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
     */
    public function testGetElementCreatedEdit(int $entry_id) {

        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" id=\"financesRecurringForm\" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "type" => 0,
            "category" => null,
            "description" => "Test Updated",
            "value" => 20,
            "paymethod" => "",
            "common" => 0,
            "common_value" => "",
            "notice" => "testing",
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 3
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
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {
        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data, $category_name = "") {
        $value = number_format($data["value"], 2);

        $matches = [];
        $re = '/<tr>\s*<td>Ausgabe<\/td>\s*<td>' . preg_quote($category_name) . '<\/td>\s*<td>' . preg_quote($data["description"]) . '<\/td>\s*<td>' . preg_quote($value) . '<\/td>\s*<td>' . preg_quote($data["start"]) . '<\/td>\s*<td>' . preg_quote($data["end"]) . '<\/td>\s*<td>' . $data['multiplier'] . ' x Tag' . '<\/td>\s*<td><\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
