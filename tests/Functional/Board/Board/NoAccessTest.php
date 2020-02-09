<?php

namespace Tests\Functional\Board\Board;

use Tests\Functional\Board\BoardTestBase;

class NoAccessTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 2;
    protected $TEST_BOARD_HASH = "DEFdef456";

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
        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_BOARD_HASH) {
                $this->fail("Hash found");
            }
        }

        // get multiple tokens
        $csrf = $this->extractJSCSRF($response);
        $tokens = $this->getCSRFTokens($csrf);
        return $tokens;
    }

    /**
     * Edit board
     */
    public function testGetParentEdit() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_BOARD_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        //$body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * 
     * @depends testGetParentInList
     */
    public function testPostParentSave(array $tokens) {
        $data = [
            "id" => $this->TEST_BOARD_ID,
            "hash" => $this->TEST_BOARD_HASH,
            "name" => "Test Board Update",
            "users" => [1]
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
     * View Trip
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

}
