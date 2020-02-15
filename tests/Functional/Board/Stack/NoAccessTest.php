<?php

namespace Tests\Functional\Board\Stack;

use Tests\Functional\Board\BoardTestBase;

class NoAccessTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_STACK_ID = 1;
    protected $uri_save = "/boards/stacks/save/";
    protected $uri_delete = "/boards/stacks/delete/";
    protected $uri_edit = "/boards/stacks/data/";

    protected function setUp(): void {
        $this->login("user2", "user2");
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
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     * Create the stack
     */
    public function testPostChildSave() {
        $data = [
            "name" => "Test Stack 2",
            "board" => $this->TEST_BOARD_ID,
            "id" => null,
            "position" => '1'
        ];
        $response = $this->request('POST', $this->uri_save, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * Get stack data
     */
    public function testGetChildData() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_STACK_ID);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /**
     * Update / Check Update
     */
    public function testPostChildUpdate() {
        $data = [
            "name" => "Test Stack 2 Updated",
            "board" => $this->TEST_BOARD_ID,
            "id" => $this->TEST_STACK_ID,
            "position" => '2'
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_STACK_ID, $data);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /**
     * Delete stack
     */
    public function testDeleteChild() {

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_STACK_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
