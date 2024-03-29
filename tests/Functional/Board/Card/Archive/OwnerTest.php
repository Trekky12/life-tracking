<?php

namespace Tests\Functional\Board\Card\Archive;

use Tests\Functional\Board\BoardTestBase;

class OwnerTest extends BoardTestBase {

    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_CARD_ID = 1;
    protected $TEST_CARD_TITLE = "Test Card";

    protected $uri_archive = "/boards/card/archive/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Archive
     */
    public function testArchive() {

        $data = [
            "archive" => 1
        ];

        $response = $this->request('POST', $this->uri_archive . $this->TEST_CARD_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_archived", $json);
        $this->assertTrue($json["is_archived"]);
    }

    /**
     * Look for archived item
     * @depends testArchive
     */
    public function testArchivedItem() {

        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("stacks", $json);
        $this->assertIsArray($json["stacks"]);

        foreach($json["stacks"] as $stack){
            $this->assertIsArray($stack);
            $this->assertArrayHasKey("cards", $stack);

            foreach($stack["cards"] as $card){
                $this->assertIsArray($card);
                $this->assertArrayHasKey("id", $card);
                $this->assertArrayHasKey("title", $card);

                if($card["title"] == $this->TEST_CARD_TITLE){
                    $this->assertEquals(1, $card["archive"]);
                }
            }
        }
    }

    /**
     * Unarchive
     */
    public function testUnArchive() {
        $data = [
            "archive" => 0
        ];

        $response = $this->request('POST', $this->uri_archive . $this->TEST_CARD_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_archived", $json);
        $this->assertTrue($json["is_archived"]);
    }

    /**
     * Look for unarchived item
     * @depends testUnArchive
     */
    public function testUnArchivedItem() {

        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("stacks", $json);
        $this->assertIsArray($json["stacks"]);

        foreach($json["stacks"] as $stack){
            $this->assertIsArray($stack);
            $this->assertArrayHasKey("cards", $stack);

            foreach($stack["cards"] as $card){
                $this->assertIsArray($card);
                $this->assertArrayHasKey("id", $card);
                $this->assertArrayHasKey("title", $card);

                if($card["title"] == $this->TEST_CARD_TITLE){
                    $this->assertEquals(0, $card["archive"]);
                }
            }
        }
    }

}
