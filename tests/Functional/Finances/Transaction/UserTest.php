<?php

namespace Tests\Functional\Finances\Transaction;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/finances/accounts/ABCabc123/view/";
    protected $uri_edit = "/finances/transactions/edit/";
    protected $uri_save = "/finances/transactions/save/";
    protected $uri_delete = "/finances/transactions/delete/";
    protected $uri_accounts = "/finances/accounts/";

    protected $TEST_FINANCE_ACCOUNT_1_ID = 1;
    protected $TEST_FINANCE_ACCOUNT_2_ID = 3;

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
        $this->assertStringContainsString('<table id="finance_transaction_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST">', $body);
    }



    public function testPostAddElement() {

        $data = [
            "description" => "Test Transaction 2",
            "date" => date('Y-m-d'),
            "time" => date("H:i:s"),
            "value" => rand(0, 10000) / 100,
            "account_from" => $this->TEST_FINANCE_ACCOUNT_1_ID,
            "account_to" => $this->TEST_FINANCE_ACCOUNT_2_ID
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringStartsWith($this->uri_accounts, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostAddElement')]
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
     */
    #[Depends('testAddedElement')]
    #[Depends('testPostAddElement')]
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

    #[Depends('testGetElementCreatedEdit')]
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "description" => "Test Transaction 2 Updated",
            "date" => date('Y-m-d'),
            "time" => date("H:i:s"),
            "value" => rand(0, 10000) / 100,
            "account_from" => $this->TEST_FINANCE_ACCOUNT_1_ID,
            "account_to" => $this->TEST_FINANCE_ACCOUNT_2_ID
        ];

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringStartsWith($this->uri_accounts, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostElementCreatedSave')]
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    #[Depends('testGetElementCreatedEdit')]
    #[Depends('testPostElementCreatedSave')]
    public function testChanges(int $entry_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    #[Depends('testGetElementUpdated')]
    public function testDeleteElement(int $entry_id) {
        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {

        $data["account_from_name"] = "Test Account";
        $data["account_to_name"] = "Test Account 2";

        $matches = [];
        $re = '/<tr data-confirmed="0">\s*<td>.*?<\/td>\s*<td>' . preg_quote($data["date"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["time"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["description"] ?? '') . '<\/td>\s*<td>' . number_format($data["value"], 2) . '<\/td>\s*<td>' . preg_quote($data["account_from_name"] ?? '') . '<\/td>\s*<td>' . preg_quote($data["account_to_name"] ?? '') . '<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)\?account=(?<hash>[\w]+)">.*?<\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete" data-confirm=".*?">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
