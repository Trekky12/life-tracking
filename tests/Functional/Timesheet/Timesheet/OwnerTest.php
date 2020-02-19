<?php

namespace Tests\Functional\Timesheet\Timesheet;

use Tests\Functional\Timesheet\TimesheetTestBase;

class OwnerTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Add new Sheet
     */
    public function testGetChildEdit() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURIChildSave($this->TEST_PROJECT_HASH) . "\" method=\"POST\">", $body);
    }

    /**
     * Create the sheet
     */
    public function testPostChildSave() {
        $data = [
            "start" => date('Y-m-d') . " 12:00:00",
            "end" => date('Y-m-d') . " 14:10:00",
            "notice" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_PROJECT_HASH), $response->getHeaderLine("Location"));

        $data["diff"] = "02:10:00";

        return $data;
    }

    /**
     * Is the created sheet now in the table?
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Update sheet
     */

    /**
     * Edit Sheet
     * @depends testGetChildCreated
     * @depends testPostChildSave
     */
    public function testGetChildCreatedEdit(int $timesheet_id, $data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $timesheet_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $timesheet_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        unset($data["diff"]);
        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(int $timesheet_id) {
        $data = [
            "id" => $timesheet_id,
            "start" => date('Y-m-d') . " 10:02:00",
            "end" => date('Y-m-d') . " 18:55:00",
            "notice" => "Testnotice"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $timesheet_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_PROJECT_HASH), $response->getHeaderLine("Location"));

        $data["diff"] = "08:53:00";

        return $data;
    }

    /**
     * Is the sheet data updated in the table?
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetChildUpdated
     * @depends testPostChildCreatedSave
     */
    public function testChanges(int $timesheet_id, $data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $timesheet_id);

        $body = (string) $response->getBody();
        unset($data["diff"]);
        $this->compareInputFields($body, $data);
    }

    /**
     * Delete sheet
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(int $timesheet_id) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $timesheet_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
