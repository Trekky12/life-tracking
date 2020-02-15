<?php

namespace Tests\Functional\Trip\Event;

use Tests\Functional\Trip\TripTestBase;

class MemberTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Add new event
     */
    public function testGetChildEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_TRIP_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURIChildSave($this->TEST_TRIP_HASH) . "\" method=\"POST\">", $body);
    }

    /**
     * Create the event
     */
    public function testPostChildSave() {
        $data = [
            "name" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_TRIP_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_TRIP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the created event
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data["name"]);

        $this->assertArrayHasKey("id", $row);

        return intval($row["id"]);
    }

    /**
     * Update Event
     */

    /**
     * Edit Event
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(int $child_id) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_TRIP_HASH) . $child_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $child_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(int $child_id) {
        $data = [
            "id" => $child_id,
            "name" => "Testevent updated"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_TRIP_HASH) . $child_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_TRIP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the event data updated?
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $data) {

        $response = $this->request('GET', $this->getURIView($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data["name"]);

        $this->assertArrayHasKey("id", $row);

        return intval($row["id"]);
    }

    /**
     * Delete event
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(int $child_id) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_TRIP_HASH) . $child_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
