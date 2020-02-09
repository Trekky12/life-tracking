<?php

namespace Tests\Functional\Board\Label;

use Tests\Functional\Board\BoardTestBase;

class NoAccessTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_LABEL_ID = 1;
    protected $uri_save = "/boards/labels/save/";
    protected $uri_delete = "/boards/labels/delete/";
    protected $uri_edit = "/boards/labels/data/";

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

        // get multiple tokens
        $csrf = $this->extractJSCSRF($response);
        $tokens = $this->getCSRFTokens($csrf);
        return $tokens;
    }

    /**
     * Create the label
     * @depends testGetViewBoard
     */
    public function testPostChildSave(array $tokens) {
        $data = [
            "name" => "Test Label 2",
            "board" => $this->TEST_BOARD_ID,
            "id" => null,
            "text_color" => '#000FFF',
            "background_color" => '#CCCCCC',
        ];
        $response = $this->request('POST', $this->uri_save, array_merge($data, $tokens[0]));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * Get Label data
     */
    public function testGetChildData() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_LABEL_ID);

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
     * @depends testGetViewBoard
     */
    public function testPostChildUpdate(array $tokens) {
        $data = [
            "name" => "Test Label 2 Updated",
            "board" => $this->TEST_BOARD_ID,
            "id" => $this->TEST_LABEL_ID,
            "text_color" => '#000EEE',
            "background_color" => '#000000',
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_LABEL_ID, array_merge($data, $tokens[1]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /**
     * Delete label
     * @depends testGetViewBoard
     */
    public function testDeleteChild(array $tokens) {

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_LABEL_ID, $tokens[2]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
