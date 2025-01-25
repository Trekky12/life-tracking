<?php

namespace Tests\Functional\Workout\Template;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/templates/";
    protected $uri_edit = "/workouts/templates/manage/edit/";
    protected $uri_save = "/workouts/templates/manage/save/";
    protected $uri_delete = "/workouts/templates/manage/delete/";
    protected $uri_view = "/workouts/templates/HASH/view/";
    protected $TEST_TEMPLATE_ID = 2;
    protected $TEST_TEMPLATE_HASH = "ABCabc456";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, ["name" => "Test workout template plan"]);

        $this->assertArrayHasKey("hash", $row);
        $this->assertEquals($row["hash"], $this->TEST_TEMPLATE_HASH);
    }

    /** 
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_TEMPLATE_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }



    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_TEMPLATE_ID,
            "name" => "Test Workout Plan 1 Updated",
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

        $response = $this->request('POST', $this->uri_save . $this->TEST_TEMPLATE_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_TEMPLATE_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /** 
     * View Plan
     */
    public function testGetView() {
        $response = $this->request('GET', $this->getURIView($this->TEST_TEMPLATE_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div class=\"workout-selection view\">", $body);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/workouts\/templates\/(?<hash>.*)\/view\/">' . preg_quote($data["name"] ?? '') . '<\/a>\s*<\/td>\s*<td>[0-9\s]*<\/td>\s*<td>[0-9\s]*<\/td>\s*<td><\/td>\s*<td><\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
