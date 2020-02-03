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

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the sheet
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $csrf_data) {
        $data = [
            "start" => date('Y-m-d') . " 12:00",
            "end" => date('Y-m-d') . " 14:10",
            "notice" => "Test",
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH), array_merge($data, $csrf_data));

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

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Update sheet
     */

    /**
     * Edit Sheet
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(array $result_data_child) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $result_data_child["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_child["id"] . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $result = [];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(array $result_data_child) {
        $data = [
            "id" => $result_data_child["id"],
            "start" => date('Y-m-d') . " 10:02",
            "end" => date('Y-m-d') . " 18:55",
            "notice" => "Testnotice"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

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

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Delete sheet
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(array $result_data_child) {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
