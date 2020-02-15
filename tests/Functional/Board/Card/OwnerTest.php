<?php

namespace Tests\Functional\Board\Card;

use Tests\Functional\Board\BoardTestBase;

class OwnerTest extends BoardTestBase {

    protected $TEST_STACK_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $uri_save = "/boards/card/save/";
    protected $uri_delete = "/boards/card/delete/";
    protected $uri_edit = "/boards/card/data/";

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
        $this->assertStringContainsString('<div class="board-header">', $body);
    }

    /**
     * Create the card
     */
    public function testPostChildSave() {
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
        $response = $this->request('POST', $this->uri_save, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * Is the created card visible?
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getCard($body, $data["title"]);

        $this->assertArrayHasKey("id", $row);

        return intval($row["id"]);
    }

    /**
     * Get card data
     * @depends testPostChildSave
     * @depends testGetChildCreated
     */
    public function testGetChildData(array $data, int $card_id) {

        $response = $this->request('GET', $this->uri_edit . $card_id);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["title"], $json["entry"]["title"]);
        $this->assertSame($data["position"], $json["entry"]["position"]);
        $this->assertSame($data["stack"], intval($json["entry"]["stack"]));
        $this->assertSame($data["date"], $json["entry"]["date"]);
        $this->assertSame($data["time"], $json["entry"]["time"]);
        $this->assertSame($data["description"], $json["entry"]["description"]);
        $this->assertSame($data["archive"], $json["entry"]["archive"]);
        $this->assertSame($data["users"], $json["entry"]["users"]);
        $this->assertSame($data["labels"], $json["entry"]["labels"]);
        $this->assertSame($card_id, intval($json["entry"]["id"]));
    }

    /**
     * Update / Check Update
     * @depends testGetChildCreated
     */
    public function testPostChildUpdate(int $card_id) {
        $data = [
            "title" => "Test Card 2 Updated",
            "stack" => $this->TEST_STACK_ID,
            "id" => $card_id,
            "position" => '1',
            "date" => date('Y-m-d'),
            "time" => date('H:i:s'),
            "description" => "Test description",
            "archive" => '0',
            "users" => [1, 2],
            "labels" => [1]
        ];
        $response = $this->request('POST', $this->uri_save . $card_id, $data);

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
    public function testGetChildDataUpdated(array $data, int $card_id) {

        $response = $this->request('GET', $this->uri_edit . $card_id);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["title"], $json["entry"]["title"]);
        $this->assertSame($data["position"], $json["entry"]["position"]);
        $this->assertSame($data["stack"], intval($json["entry"]["stack"]));
        $this->assertSame($data["date"], $json["entry"]["date"]);
        $this->assertSame($data["time"], $json["entry"]["time"]);
        $this->assertSame($data["description"], $json["entry"]["description"]);
        $this->assertSame($data["archive"], $json["entry"]["archive"]);
        $this->assertSame($data["users"], $json["entry"]["users"]);
        $this->assertSame($data["labels"], $json["entry"]["labels"]);
        $this->assertSame($card_id, intval($json["entry"]["id"]));
    }

    /**
     * Delete stack
     * @depends testGetChildCreated
     */
    public function testDeleteChild(int $card_id) {
        $response = $this->request('DELETE', $this->uri_delete . $card_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
