<?php

namespace Tests\Functional\Board\Stack;

use Tests\Functional\Board\BoardTestBase;

class OwnerTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $uri_save = "/boards/stacks/save/";
    protected $uri_delete = "/boards/stacks/delete/";

    protected function setUp(): void {
        $this->login("admin", "admin");
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
        $this->assertStringContainsString('<body class="boards boards-view">', $body);
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
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $data;
    }

    /**
     * Is the created stack visible?
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("stacks", $json);
        $this->assertIsArray($json["stacks"]);

        $this_stack = null;
        foreach($json["stacks"] as $stack){
            $this->assertIsArray($stack);
            $this->assertArrayHasKey("name", $stack);

            if($stack["name"] == $data["name"]){
                $this_stack = $stack;
                break;
            }
        }
        $this->assertNotNull($this_stack);
        $this->assertSame($data["name"], $this_stack["name"]);
        $this->assertSame($data["position"], $this_stack["position"]);

        return intval($this_stack["id"]);
    }

    /**
     * Update / Check Update
     * @depends testGetChildCreated
     */
    public function testPostChildUpdate(int $stack_id) {
        $data = [
            "name" => "Test Stack 2 Updated",
            "board" => $this->TEST_BOARD_ID,
            "id" => $stack_id,
            "position" => '2'
        ];
        $response = $this->request('POST', $this->uri_save . $stack_id, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $data;
    }

    /**
     * Get updated data
     * @depends testPostChildUpdate
     * @depends testGetChildCreated
     */
    public function testGetChildDataUpdated(array $data, int $stack_id) {

        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("stacks", $json);
        $this->assertIsArray($json["stacks"]);

        $this_stack = null;
        foreach($json["stacks"] as $stack){
            if($stack["id"] == $stack_id){
                $this_stack = $stack;
                break;
            }
        }
        $this->assertNotNull($this_stack);
    
        $this->assertSame($data["name"], $this_stack["name"]);
        $this->assertSame($data["position"], $this_stack["position"]);
        $this->assertSame($stack_id, intval($this_stack["id"]));
    }

    /**
     * Delete stack
     * @depends testGetChildCreated
     */
    public function testDeleteChild(int $stack_id) {
        $response = $this->request('DELETE', $this->uri_delete . $stack_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
