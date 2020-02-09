<?php

namespace Tests\Functional\Board\Card\Archive;

use Tests\Functional\Board\BoardTestBase;

class NoAccessTest extends BoardTestBase {

    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_CARD_ID = 1;
    protected $TEST_CARD_TITLE = "Test Card";
    protected $uri_archive = "/boards/card/archive/";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Archive
     */
    public function testArchive() {

        $response1 = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));
        $token = $this->extractJSCSRF($response1);

        $data = [
            "archive" => 1
        ];

        $response = $this->request('POST', $this->uri_archive . $this->TEST_CARD_ID, array_merge($token, $data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    /**
     * Unarchive
     */
    public function testUnArchive() {

        $response1 = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));
        $token = $this->extractJSCSRF($response1);

        $data = [
            "archive" => 0
        ];

        $response = $this->request('POST', $this->uri_archive . $this->TEST_CARD_ID, array_merge($token, $data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
