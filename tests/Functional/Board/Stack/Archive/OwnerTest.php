<?php

namespace Tests\Functional\Board\Stack\Archive;

use Tests\Functional\Board\BoardTestBase;

class OwnerTest extends BoardTestBase {

    protected $TEST_BOARD_HASH = "ABCabc123";
    protected $TEST_STACK_ID = 1;
    protected $TEST_STACK_NAME = "Test Stack";

    protected $uri_archive = "/boards/stacks/archive/";

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

        $response1 = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));
        $token = $this->extractJSCSRF($response1);

        $data = [
            "archive" => 1
        ];

        $response = $this->request('POST', $this->uri_archive . $this->TEST_STACK_ID, array_merge($token, $data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_archived", $json);
        $this->assertTrue($json["is_archived"]);
    }

    /**
     * Look for archived item
     */
    public function testArchivedItem() {

        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();

        $row = $this->getStack($body, $this->TEST_STACK_NAME);
        $this->assertTrue(empty($row));
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

        $response = $this->request('POST', $this->uri_archive . $this->TEST_STACK_ID, array_merge($token, $data));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_archived", $json);
        $this->assertTrue($json["is_archived"]);
    }

    /**
     * Look for unarchived item
     */
    public function testUnArchivedItem() {

        $response = $this->request('GET', $this->getURIView($this->TEST_BOARD_HASH));

        $body = (string) $response->getBody();

        $row = $this->getStack($body, $this->TEST_STACK_NAME);
        $this->assertArrayHasKey("id", $row);
    }

}
