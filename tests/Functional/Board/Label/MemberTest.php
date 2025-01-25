<?php

namespace Tests\Functional\Board\Label;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Board\BoardTestBase;

class MemberTest extends BoardTestBase {

    protected $TEST_BOARD_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $uri_save = "/boards/labels/save/";
    protected $uri_delete = "/boards/labels/delete/";
    protected $uri_edit = "/boards/labels/data/";

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
        $this->assertStringContainsString('<body class="boards boards-view', $body);
    }

    /** 
     * Create the label
     */
    public function testPostChildSave() {
        $data = [
            "name" => "Test Label 2",
            "board" => $this->TEST_BOARD_ID,
            "id" => null,
            "text_color" => '#000FFF',
            "background_color" => '#CCCCCC',
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
     * Is the created label visible?
     */
    #[Depends('testPostChildSave')]
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getLabel($body, $data);

        $this->assertArrayHasKey("id", $row);

        return intval($row["id"]);
    }

    /** 
     * Get Label data
     */
    #[Depends('testPostChildSave')]
    #[Depends('testGetChildCreated')]
    public function testGetChildData(array $data, int $label_id) {

        $response = $this->request('GET', $this->uri_edit . $label_id);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["name"], $json["entry"]["name"]);
        $this->assertSame($data["background_color"], $json["entry"]["background_color"]);
        $this->assertSame($data["text_color"], $json["entry"]["text_color"]);
        $this->assertSame($label_id, intval($json["entry"]["id"]));
    }

    /** 
     * Update / Check Update
     */
    #[Depends('testGetChildCreated')]
    public function testPostChildUpdate(int $label_id) {
        $data = [
            "name" => "Test Label 2 Updated",
            "board" => $this->TEST_BOARD_ID,
            "id" => $label_id,
            "text_color" => '#000EEE',
            "background_color" => '#000000',
        ];
        $response = $this->request('POST', $this->uri_save . $label_id, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $data;
    }

    /** 
     * Get Label data Updated
     */
    #[Depends('testPostChildUpdate')]
    #[Depends('testGetChildCreated')]
    public function testGetChildDataUpdated(array $data, int $label_id) {

        $response = $this->request('GET', $this->uri_edit . $label_id);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($data["name"], $json["entry"]["name"]);
        $this->assertSame($data["background_color"], $json["entry"]["background_color"]);
        $this->assertSame($data["text_color"], $json["entry"]["text_color"]);
        $this->assertSame($label_id, intval($json["entry"]["id"]));
    }

    /** 
     * Delete label
     */
    #[Depends('testGetChildCreated')]
    public function testDeleteChild(int $label_id) {
        $response = $this->request('DELETE', $this->uri_delete . $label_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
