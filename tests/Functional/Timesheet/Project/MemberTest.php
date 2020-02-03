<?php

namespace Tests\Functional\Timesheet\Project;

use Tests\Functional\Timesheet\TimesheetTestBase;

class MemberTest extends TimesheetTestBase {

    protected $TEST_PROJECT_ID = 1;
    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = [];
        $re = '/<tr data-id="(?<id>[0-9]*)">\s*<td data-title="Name"><a href="\/timesheets\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*<td>\s*<a href="(?<edit>.*)"><span class="fa fa-pencil-square-o fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="(?<delete>.*)" class="btn-delete"><span class="fa fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_PROJECT_HASH) {
                $this->fail("Hash found");
            }
        }
    }

    /**
     * Edit project
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_PROJECT_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * 
     * @depends testGetParentEdit
     */
    public function testPostParentSave(array $token) {

        $data = [
            "id" => $this->TEST_PROJECT_ID,
            "hash" => $this->TEST_PROJECT_HASH,
            "name" => "Testproject Update",
            "users" => [1, 3]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_PROJECT_ID, array_merge($data, $token));

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * Delete
     * @depends testPostParentSave
     */
    public function testDeleteParent(array $token) {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_PROJECT_ID, $token);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /**
     * View Project (members can access)
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("timesheets_sheets_table", $body);
    }

}
