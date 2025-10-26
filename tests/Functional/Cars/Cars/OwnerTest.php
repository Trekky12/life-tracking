<?php

namespace Tests\Functional\Cars\Cars;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Cars\CarTestBase;

class OwnerTest extends CarTestBase {

    protected $TEST_CAR_HASH = "ABCabc123";

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
        $this->assertStringContainsString('<table id="cars_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST">', $body);
    }



    public function testPostAddElement() {

        $data = [
            "name" => "Test Car 2",
            "users" => [1, 2],
            "mileage_per_year" => 10000,
            "mileage_term" => 4,
            "mileage_start_date" => date('Y-m-d')
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostAddElement')]
    public function testAddedElement($data) {
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
     * Edit created element
     */
    #[Depends('testPostAddElement')]
     #[Depends('testAddedElement')]
    public function testGetElementCreatedEdit($data, array $result_data) {

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\" type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\"  value=\"" . $data["name"] . "\" >", $body);
        
        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">.*<input name="hash" type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
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

    #[Depends('testGetElementCreatedEdit')]
    public function testPostElementCreatedSave(array $result_data) {

        $data = [
            "id" => $result_data["id"],
            "hash" => $result_data["hash"],
            "name" => "Test Car 2 Updated",
            "users" => [1, 2],
            "mileage_per_year" => 15000,
            "mileage_term" => 4,
            "mileage_start_date" => date('Y-m-d')
        ];

        $response = $this->request('POST', $this->uri_save .  $result_data["id"], $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testGetElementCreatedEdit')]
    #[Depends('testPostElementCreatedSave')]
    public function testChanges(array $result_data, $data) {
        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    #[Depends('testAddedElement')]
    public function testGetViewParent(array $result_data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("fuel_table", $body);
    }

    #[Depends('testAddedElement')]
    public function testDeleteElement(array $result_data) {

        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
