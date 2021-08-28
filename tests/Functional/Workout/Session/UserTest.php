<?php

namespace Tests\Functional\Workout\Session;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_child_overview = "/workouts/HASH/sessions/";
    protected $uri_child_edit = "/workouts/HASH/sessions/edit/";
    protected $uri_child_save = "/workouts/HASH/sessions/save/";
    protected $uri_child_delete = "/workouts/HASH/sessions/delete/";
    protected $uri_child_view = "/workouts/HASH/sessions/view/";
    protected $TEST_PLAN_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="workouts_sessions_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->getURIChildSave($this->TEST_PLAN_HASH) . '" method="POST">', $body);
    }

    public function testPostAddElement() {

        $data = [
            "date" => date('Y-m-d'),
            "start_time" => "12:00:00",
            "end_time" => "13:00:00",
            "notice" => "test",
            "exercises" => [
                0 => [
                    "id" => 3,
                    "type" => "exercise",
                    "is_child" => 0,
                    "notice" => ''
                ],
                1 => [
                    "id" => 2,
                    "type" => "exercise",
                    "is_child" => 0,
                    "sets" => [
                        0 => [
                            "repeats" => 1,
                            "weight" => 2
                        ],
                        1 => [
                            "repeats" => 3,
                            "weight" => 4
                        ]
                    ],
                    "notice" => ''
                ],
                2 => [
                    "id" => 1,
                    "type" => "exercise",
                    "is_child" => 0,
                    "notice" => ''
                ]
            ]
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PLAN_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_PLAN_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Edit created element
     * @depends testAddedElement
     * @depends testPostAddElement
     */
    public function testGetElementCreatedEdit(int $entry_id, array $data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PLAN_HASH) . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {

        $data = [
            "id" => $entry_id,
            "date" => date('Y-m-d'),
            "start_time" => "12:00:00",
            "end_time" => "13:00:00",
            "notice" => "test",
            "exercises" => [
                0 => [
                    "id" => 1,
                    "type" => "exercise",
                    "is_child" => 0,
                    "notice" => ''
                ],
                1 => [
                    "id" => 2,
                    "type" => "exercise",
                    "is_child" => 0,
                    "sets" => [
                        0 => [
                            "repeats" => 1,
                            "weight" => 2
                        ],
                        1 => [
                            "repeats" => 3,
                            "weight" => 4
                        ]
                    ],
                    "notice" => ''
                ]
            ]
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PLAN_HASH) . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_PLAN_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PLAN_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetElementCreatedEdit
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $entry_id, $data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PLAN_HASH) . $entry_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PLAN_HASH) . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="' . str_replace('/', "\/", $this->getURIChildView($this->TEST_PLAN_HASH)) . '(?<id_view>.*)">' . preg_quote($data["date"]) . '<\/a><\/td>\s*<td>\s*<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->getURIChildEdit($this->TEST_PLAN_HASH)) . '(?<id_edit>[0-9]*)">.*?<\/a><\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($this->TEST_PLAN_HASH)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getURIChildView($hash) {
        return str_replace("HASH", $hash, $this->uri_child_view);
    }

}
