<?php

namespace Tests\Functional\Board\Card;

use Tests\Functional\Board\BoardTestBase;

class NoAccessTest extends BoardTestBase {

    protected $TEST_STACK_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_CARD_ID = 1;
    protected $uri_save = "/boards/card/save/";
    protected $uri_delete = "/boards/card/delete/";
    protected $uri_edit = "/boards/card/data/";

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
     * Create the card
     * @depends testGetViewBoard
     */
    public function testPostChildSave(array $tokens) {
        $data = [
            "title" => "Test Card 2",
            "stack" => $this->TEST_STACK_ID,
            "id" => null,
            "position" => '1',
            "date" => date('Y-m-d'),
            "time" => date('H:i:s'),
            "description" => "Test description",
            "archive" => '0',
            "users" => [1, 2],
            "labels" => [1]
        ];
        $response = $this->request('POST', $this->uri_save, array_merge($data, $tokens[0]));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * Get card data
     */
    public function testGetChildData() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_CARD_ID);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

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
            "title" => "Test Card 2 Updated",
            "stack" => $this->TEST_STACK_ID,
            "id" => $this->TEST_CARD_ID,
            "position" => '1',
            "date" => date('Y-m-d'),
            "time" => date('H:i:s'),
            "description" => "Test description",
            "archive" => '0',
            "users" => [1, 2],
            "labels" => [1]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_CARD_ID, array_merge($data, $tokens[1]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /**
     * Delete card
     * @depends testGetViewBoard
     */
    public function testDeleteChild(array $tokens) {

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CARD_ID, $tokens[2]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
