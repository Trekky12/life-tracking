<?php

namespace Tests\Functional\Workout\Plan;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/plans/";
    protected $uri_edit = "/workouts/plans/edit/";
    protected $uri_save = "/workouts/plans/save/";
    protected $uri_delete = "/workouts/plans/delete/";
    protected $uri_view = "/workouts/HASH/view/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="workouts_plans_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Workout Plan 1",
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
                ],
                3 => [
                    "type" => "day",
                    "notice" => ''
                ],
                4 => [
                    "type" => "superset"
                ],
                5 => [
                    "id" => 1,
                    "type" => "exercise",
                    "is_child" => 1,
                    "notice" => ''
                ],
                6 => [
                    "id" => 2,
                    "type" => "exercise",
                    "is_child" => 1,
                    "notice" => ''
                ]
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->uri_overview);

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

        $response = $this->request('GET', $this->uri_edit . $entry_id);

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
            "name" => "Test Workout Plan 1 Updated",
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

        $response = $this->request('POST', $this->uri_save . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);
        $this->assertArrayHasKey("hash", $row);

        $result = [];
        $result["hash"] = $row["hash"];
        $result["id"] = intval($row["id_edit"]);

        return $result;
    }

    /**
     * @depends testGetElementCreatedEdit
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $entry_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $entry_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * View Plan
     * @depends testGetElementUpdated
     */
    public function testGetView(array $result_data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div class=\"workout-selection view\">", $body);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(array $result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/workouts\/(?<hash>.*)\/view\/">' . preg_quote($data["name"]) . '<\/a>\s*<\/td>\s*<td>[0-9\s]*<\/td>\s*<td>[0-9\s]*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
