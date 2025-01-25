<?php

namespace Tests\Functional\Board\Card;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Board\BoardTestBase;

class MemberTest extends BoardTestBase {

    protected $TEST_STACK_ID = 1;
    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $uri_save = "/boards/card/save/";
    protected $uri_delete = "/boards/card/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetViewBoard() {
        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<body class="boards boards-view', $body);
    }

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
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $data;
    }


    /** 
     * Is the created card visible?
     */
    #[Depends('testPostChildSave')]
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("stacks", $json);
        $this->assertIsArray($json["stacks"]);

        $this_card = null;
        foreach ($json["stacks"] as $stack) {
            $this->assertIsArray($stack);
            $this->assertArrayHasKey("name", $stack);

            foreach ($stack["cards"] as $card) {
                $this->assertIsArray($card);
                $this->assertArrayHasKey("id", $card);
                $this->assertArrayHasKey("title", $card);

                if ($card["title"] == $data["title"]) {
                    $this_card = $card;
                    break;
                }
            }
        }
        $this->assertNotNull($this_card);
        $this->assertSame($data["title"], $this_card["title"]);
        $this->assertSame($data["position"], $this_card["position"]);
        $this->assertSame($data["stack"], intval($this_card["stack"]));
        $this->assertSame($data["date"], $this_card["date"]);
        $this->assertSame($data["time"], $this_card["time"]);
        $this->assertSame($data["description"], $this_card["description"]);
        $this->assertSame($data["archive"], $this_card["archive"]);
        $this->assertSame($data["users"], $this_card["users"]);
        $this->assertSame($data["labels"], $this_card["labels"]);

        return intval($this_card["id"]);
    }


    /** 
     * Update / Check Update
     */
    #[Depends('testGetChildCreated')]
    public function testPostChildUpdate(int $child_id) {
        $data = [
            "title" => "Test Card 2 Updated",
            "stack" => $this->TEST_STACK_ID,
            "id" => $child_id,
            "position" => '1',
            "date" => date('Y-m-d'),
            "time" => date('H:i:s'),
            "description" => "Test description",
            "archive" => '0',
            "users" => [1, 2],
            "labels" => [1]
        ];
        $response = $this->request('POST', $this->uri_save . $child_id, $data);

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
     */
    #[Depends('testPostChildUpdate')]
    #[Depends('testGetChildCreated')]
    public function testGetChildDataUpdated(array $data, int $card_id) {

        $response = $this->request('GET', $this->getURIData($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this_card = null;
        foreach ($json["stacks"] as $stack) {
            $this->assertIsArray($stack);
            $this->assertArrayHasKey("name", $stack);

            foreach ($stack["cards"] as $card) {
                $this->assertIsArray($card);
                $this->assertArrayHasKey("id", $card);
                $this->assertArrayHasKey("title", $card);

                if ($card["id"] == $card_id) {
                    $this_card = $card;
                    break;
                }
            }
        }
        $this->assertNotNull($this_card);
        $this->assertSame($data["title"], $this_card["title"]);
        $this->assertSame($data["position"], $this_card["position"]);
        $this->assertSame($data["stack"], intval($this_card["stack"]));
        $this->assertSame($data["date"], $this_card["date"]);
        $this->assertSame($data["time"], $this_card["time"]);
        $this->assertSame($data["description"], $this_card["description"]);
        $this->assertSame($data["archive"], $this_card["archive"]);
        $this->assertSame($data["users"], $this_card["users"]);
        $this->assertSame($data["labels"], $this_card["labels"]);
    }

    /** 
     * Delete card
     */
    #[Depends('testGetChildCreated')]
    public function testDeleteChild(int $child_id) {

        $response = $this->request('DELETE', $this->uri_delete . $child_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
