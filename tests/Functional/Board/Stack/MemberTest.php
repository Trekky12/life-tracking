<?php

namespace Tests\Functional\Board\Stack;

use Tests\Functional\Board\BoardTestBase;

class MemberTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $uri_save = "/boards/stacks/save/";
    protected $uri_delete = "/boards/stacks/delete/";
    protected $uri_edit = "/boards/stacks/data/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * View Board
     */
    public function testGetViewBoard() {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div class="board-header">', $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * Create the stack
     * @depends testGetViewBoard
     */
    public function testPostChildSave(array $csrf_data) {
        $data = [
            "name" => "Test Stack 2",
            "board" => $this->TEST_BOARD_ID,
            "id" => null,
            "position" => '1'
        ];
        $response = $this->request('POST', $this->uri_save, array_merge($data, $csrf_data));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * Is the created stack visible?
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getStack($body, $data["name"]);

        $this->assertArrayHasKey("id", $row);

        $result = [];
        $result["id"] = $row["id"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Get stack data
     * @depends testPostChildSave
     * @depends testGetChildCreated
     */
    public function testGetChildData(array $data, array $result_data_child) {

        $response = $this->request('GET', $this->uri_edit . $result_data_child["id"]);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["name"], $json["entry"]["name"]);
        $this->assertSame($data["position"], $json["entry"]["position"]);
        $this->assertSame($result_data_child["id"], $json["entry"]["id"]);
    }

    /**
     * Update / Check Update
     * @depends testGetChildCreated
     */
    public function testPostChildUpdate(array $result_data_child) {
        $data = [
            "name" => "Test Stack 2 Updated",
            "board" => $this->TEST_BOARD_ID,
            "id" => $result_data_child["id"],
            "position" => '2'
        ];
        $response = $this->request('POST', $this->uri_save . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * Get updated data
     * @depends testPostChildUpdate
     * @depends testGetChildCreated
     */
    public function testGetChildDataUpdated(array $data, array $result_data_child) {

        $response = $this->request('GET', $this->uri_edit . $result_data_child["id"]);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["name"], $json["entry"]["name"]);
        $this->assertSame($data["position"], $json["entry"]["position"]);
        $this->assertSame($result_data_child["id"], $json["entry"]["id"]);
    }

    /**
     * Delete stack
     * @depends testGetChildCreated
     */
    public function testDeleteChild(array $result_data_child) {

        $response1 = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));
        $token = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->uri_delete . $result_data_child["id"], $token);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
