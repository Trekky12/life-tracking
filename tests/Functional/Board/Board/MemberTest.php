<?php

namespace Tests\Functional\Board\Board;

use Tests\Functional\Board\BoardTestBase;

class MemberTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = $this->getParents($body);
        $hashs = array_map(function($match) {
            return $match["hash"];
        }, $matches);
        $this->assertContains($this->TEST_BOARD_HASH, $hashs);

        // get multiple tokens
        $csrf = $this->extractJSCSRF($response);
        $tokens = $this->getCSRFTokens($csrf);

        return $tokens;
    }

    /**
     * Edit trip
     * 
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_BOARD_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * @depends testGetParentInList
     */
    public function testPostParentSave(array $tokens) {
        $data = [
            "id" => $this->TEST_BOARD_ID,
            "hash" => $this->TEST_BOARD_HASH,
            "name" => "Test Board Update",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_BOARD_ID, array_merge($data, $tokens[0]));

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Delete
     * @depends testGetParentInList
     */
    public function testDeleteParent(array $tokens) {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_BOARD_ID, $tokens[1]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * View trip (members can access)
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<div class="board-header">', $body);
    }

}
