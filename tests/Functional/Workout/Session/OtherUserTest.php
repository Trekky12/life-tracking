<?php

namespace Tests\Functional\Workout\Session;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {

    protected $uri_child_overview = "/workouts/HASH/sessions/";
    protected $uri_child_edit = "/workouts/HASH/sessions/edit/";
    protected $uri_child_save = "/workouts/HASH/sessions/save/";
    protected $uri_child_delete = "/workouts/HASH/sessions/delete/";
    protected $TEST_PLAN_HASH = "ABCabc123";
    protected $TEST_SESSION_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $this->assertStringContainsString("Element nicht gefunden", $body);
    }

    /**
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PLAN_HASH) . $this->TEST_SESSION_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }

    /**
     * 
     */
    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_SESSION_ID,
            "date" => date('Y-m-d'),
            "start_time" => "12:00:00",
            "end_time" => "13:00:00",
            "notice" => "test",
            "exercises" => [
                0 => [
                    "id" => 10
                ],
                1 => [
                    "id" => 2
                ],
                2 => [
                    "id" => 1
                ]
            ]
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PLAN_HASH) . $this->TEST_SESSION_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PLAN_HASH) . $this->TEST_SESSION_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
