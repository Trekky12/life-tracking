<?php

namespace Tests\Functional\Workout\Exercise;

use Tests\Functional\Base\BaseTestCase;

class AdminTest extends BaseTestCase {

    protected $uri_overview = "/workouts/exercises/manage/";
    protected $uri_edit = "/workouts/exercises/manage/edit/";
    protected $uri_save = "/workouts/exercises/manage/save/";
    protected $uri_delete = "/workouts/exercises/manage/delete/";
    protected $TEST_MAIN_MUSCLE = 1;
    protected $TEST_MAIN_BODYPART = 1;

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
        $this->assertStringContainsString('<table id="workouts_exercises_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->uri_save . '" method="POST" enctype="multipart/form-data">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Exercise Insert",
            "instructions" => "Instructinos\ntest",
            "level" => 1,
            "rating" => 2,
            "category" => 1,
            "mainMuscle" => $this->TEST_MAIN_MUSCLE,
            "muscle_groups_primary" => [2, 3],
            "muscle_groups_secondary" => [4, 5],
            "mainBodyPart" => $this->TEST_MAIN_BODYPART,
        ];

        $filename = 'exercise_image.png';
        $filename_thumb = 'exercise_image-thumbnail.png';

        $files = [
            [
                'name' => 'image',
                'contents' => __DIR__ . DIRECTORY_SEPARATOR . $filename,
                'filename' => $filename
            ],
            [
                'name' => 'thumbnail',
                'contents' => __DIR__ . DIRECTORY_SEPARATOR . $filename_thumb,
                'filename' => $filename_thumb
            ]
        ];
        
        $response = $this->request('POST', $this->uri_save, $data, [], $files);

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
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST" enctype="multipart\/form-data">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
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
            "name" => "Test Exercise Update",
            "instructions" => "Instructinos\ntest 2",
            "level" => 2,
            "rating" => 3,
            "category" => 0,
            "mainMuscle" => $this->TEST_MAIN_MUSCLE,
            "muscle_groups_primary" => [],
            "muscle_groups_secondary" => [],
            "mainBodyPart" => $this->TEST_MAIN_BODYPART,
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

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetElementUpdated
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->uri_edit . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {

        $response = $this->request('DELETE', $this->uri_delete . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"]) . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
