<?php

namespace Tests\Functional\Trip\Event;

use Tests\Functional\Trip\TripTestBase;

class OwnerTest extends TripTestBase {

    protected $TEST_TRIP_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
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
            "name" => "Testevent",
            "start_date" => date('Y-m-d'),
            "start_time" => "12:00:00",
            "start_address" => "Berlin",
            "start_lat" => 52.520007,
            "start_lng" => 13.404954,
            "end_date" => date('Y-m-d'),
            "end_time" => "14:10:00",
            "end_address" => "Berlin",
            "end_lat" => 52.520007,
            "end_lng" => 13.404954,
            "notice" => "Test",
            "type" => "EVENT"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_TRIP_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_TRIP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the created event visible?
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
     * Update event
     */

    /**
     * Edit event
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
            "name" => "Testevent Updated",
            "start_date" => date('Y-m-d'),
            "start_time" => "12:00:00",
            "start_address" => "Berlin",
            "start_lat" => 52.520007,
            "start_lng" => 13.404954,
            "end_date" => '2020-01-03',
            "end_time" => "14:10:00",
            "end_address" => null,
            "end_lat" => null,
            "end_lng" => null,
            "notice" => "Test",
            "type" => "FLIGHT"
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
