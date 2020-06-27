<?php

namespace Tests\Functional\Admin\Users;

use Tests\Functional\Base\BaseTestCase;

class AdminTest extends BaseTestCase {

    protected $uri_overview = "/users/";
    protected $uri_edit = "/users/edit/";
    protected $uri_save = "/users/save/";
    protected $uri_delete = "/users/delete/";

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
        $this->assertStringContainsString('<table id="users_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "login" => "a_test",
            "name" => "Erika",
            "lastname" => "Mustermann",
            "mail" => "test@localhost",
            "role" => "user",
            "password" => "test",
            "module_location" => 1,
            "module_finance" => 1,
            "module_cars" => 1,
            "module_boards" => 1,
            "module_crawlers" => 1,
            "module_splitbills" => 1,
            "module_trips" => 1,
            "module_timesheets" => 1,
            "force_pw_change" => 1,
            "mails_user" => 1,
            "mails_finances" => 1,
            "mails_board" => 1,
            "mails_board_reminder" => 1,
            "mails_splitted_bills" => 1,
            "start_url" => "/test"
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
    public function testGetElementCreatedEdit(int $user_id) {

        $response = $this->request('GET', $this->uri_edit . $user_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $user_id . "\">", $body);

        $matches = [];
        $re = '/<form action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $user_id) {

        $data = [
            "id" => $user_id,
            "login" => "a_test",
            "name" => "Erika",
            "lastname" => "Mustermann",
            "mail" => "test1@localhost",
            "role" => "admin",
            "password" => "",
            "module_location" => 1,
            "module_finance" => 0,
            "module_cars" => 0,
            "module_boards" => 0,
            "module_crawlers" => 0,
            "module_splitbills" => 0,
            "module_trips" => 0,
            "module_timesheets" => 0,
            "force_pw_change" => 0,
            "mails_user" => 0,
            "mails_finances" => 0,
            "mails_board" => 0,
            "mails_board_reminder" => 0,
            "mails_splitted_bills" => 0,
            "start_url" => "/test1"
        ];

        $response = $this->request('POST', $this->uri_save . $user_id, $data);

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
    public function testDeleteElement(int $user_id) {

        $response = $this->request('DELETE', $this->uri_delete . $user_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>' . $data["login"] . '<\/td>\s*<td>' . $data["name"] . '<\/td>\s*<td>' . $data["lastname"] . '<\/td>\s*<td>' . $data["mail"] . '<\/td>\s*<td>' . $data["role"] . '<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<td><a href="\/users\/(?<id_mail>[0-9]+)\/testmail"><span class="fas fa-envelope fa-lg"><\/span> Test Mail<\/a><\/td>\s*<td><a href="\/users\/(?<id_favorites>[0-9]+)\/favorites\/">mobile Favoriten<\/a><\/td>\s*<td><a href="\/users\/(?<id_applicationpasswords>[0-9]+)\/applicationpasswords\/">Anwendungspasswörter<\/a><\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
