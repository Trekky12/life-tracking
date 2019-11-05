<?php

namespace Tests\Functional\Timesheet;

class ProjectOwnerTest extends ProjectTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testRedirectTimesheetsToProjects() {
        $response = $this->runApp('GET', '/timesheets/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    public function testGetTimesheetsProjects() {
        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("timesheets_projects_table", $body);
    }

    public function testGetTimesheetsProjectsAdd() {
        $response = $this->runApp('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->uri_save . "\" method=\"POST\">", $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * @depends testGetTimesheetsProjectsAdd
     */
    public function testPostTimesheetsProjectsAdd(array $csrf_data) {
        $data = ["name" => "Testproject", "users" => [10]];
        $response = $this->runApp('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostTimesheetsProjectsAdd
     */
    public function testGetCreatedTimesheetsProjects($data) {
        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getTableRowProjects($body, $data["name"]);

        $this->assertArrayHasKey("hash", $row);
        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["hash"] = $row["hash"];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Edit project
     * @depends testPostTimesheetsProjectsAdd
     * @depends testGetCreatedTimesheetsProjects
     */
    public function testGetTimesheetsProjectsEdit($data, array $result_data) {

        $response = $this->runApp('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<input name=\"hash\"  type=\"hidden\" value=\"" . $result_data["hash"] . "\">", $body);
        $this->assertStringContainsString("<input type=\"text\" class=\"form-control\" id=\"inputName\" name=\"name\" value=\"" . $data["name"] . "\">", $body);


        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-z0-9]*)" method="POST">.*<input name="id" .* type="hidden" value="(?<id>[0-9]*)">.*<input name="hash"  type="hidden" value="(?<hash>[a-zA-Z0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);
        $this->assertArrayHasKey("hash", $matches);

        $result = [];
        $result["hash"] = $matches["hash"];
        $result["id"] = $matches["id"];
        $result["csrf"] = $this->extractFormCSRF($response);

        return $result;
    }

    /**
     * 
     * @depends testGetTimesheetsProjectsEdit
     */
    public function testPostTimesheetsProjectsEdit(array $result_data) {
        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testproject Updated 2", "users" => [1, 10]];
        $response = $this->runApp('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }

    /**
     * View Project
     * @depends testGetCreatedTimesheetsProjects
     */
    public function testViewCreatedTimesheetsProjects(array $result_data) {
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("timesheets_sheets_table", $body);
    }

    /**
     * Timesheets
     */

    /**
     * Add new Sheet
     * @depends testGetCreatedTimesheetsProjects
     */
    public function testGetAddSheet($result) {
        $response = $this->runApp('GET', $this->getURISheetsEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURISheetsSave($result["hash"]) . "\" method=\"POST\">", $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the sheet
     * @depends testGetCreatedTimesheetsProjects
     * @depends testGetAddSheet
     */
    public function testPostAddSheet(array $result, array $csrf_data) {
        $data = ["start" => date('Y-m-d')." 12:00", "end" => date('Y-m-d')." 14:10", "notice" => "Test", "project" => $result["id"]];
        $response = $this->runApp('POST', $this->getURISheetsSave($result["hash"]), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "02:10:00";

        return $data;
    }

    /**
     * Is the created sheet now in the table?
     * @depends testGetCreatedTimesheetsProjects
     * @depends testPostAddSheet
     */
    public function testGetCreatedSheet(array $result_data, array $data) {
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getTableRowSheets($body, $data, $result_data["hash"]);

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
     * @depends testGetCreatedTimesheetsProjects
     * @depends testGetCreatedSheet
     */
    public function testGetSheetEdit(array $result_data_project, array $result_data_sheet) {

        $response = $this->runApp('GET', $this->getURISheetsEdit($result_data_project["hash"]) . $result_data_sheet["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_sheet["id"] . "\">", $body);
        $this->assertStringContainsString("<input name=\"project\" type=\"hidden\" value=\"" . $result_data_project["id"] . "\">", $body);

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
     * @depends testGetCreatedTimesheetsProjects
     * @depends testGetSheetEdit
     */
    public function testPostSheetEdit(array $result_data_project, array $result_data_sheet) {
        $data = ["id" => $result_data_sheet["id"], "project" => $result_data_project["id"], "start" => date('Y-m-d')." 10:02", "end" => date('Y-m-d')." 18:55", "notice" => "Testnotice"];
        $response = $this->runApp('POST', $this->getURISheetsSave($result_data_project["hash"]) . $result_data_sheet["id"], array_merge($data, $result_data_sheet["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result_data_project["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "08:53:00";

        return $data;
    }

    /**
     * Is the sheet data updated in the table?
     * @depends testGetCreatedTimesheetsProjects
     * @depends testPostSheetEdit
     */
    public function testGetSheetUpdated(array $result_data, array $data) {
        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getTableRowSheets($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Delete sheet
     * @depends testGetCreatedTimesheetsProjects
     * @depends testGetSheetUpdated
     */
    public function testDeleteSheet(array $result_data_project, array $result_data_sheet) {
        $response = $this->runApp('DELETE', $this->getURISheetsDelete($result_data_project["hash"]) . $result_data_sheet["id"], $result_data_sheet["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * Delete / clean
     * @depends testGetCreatedTimesheetsProjects
     */
    public function testDeleteCreatedTimesheetsProjects(array $result_data) {
        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * Delete Project with sheets
     */
    public function testDeleteProjectWithSheets() {

        // Open Project Add page
        $response1 = $this->runApp('GET', $this->uri_edit);
        $csrf1 = $this->extractFormCSRF($response1);

        // Add Project
        $data1 = ["name" => "Testproject delete with sheets", "users" => [10]];
        $this->runApp('POST', $this->uri_save, array_merge($data1, $csrf1));

        // get Hash/ID from Overview
        $response3 = $this->runApp('GET', $this->uri_overview);
        $row = $this->getTableRowProjects((string) $response3->getBody(), $data1["name"]);

        $project_hash = $row["hash"];
        $project_id = $row["id_edit"];

        // Open Sheet Add Page
        $response4 = $this->runApp('GET', $this->getURISheetsEdit($project_hash));
        $csrf3 = $this->extractFormCSRF($response4);

        // Add Sheet
        $data2 = ["start" => date('Y-m-d')." 10:00", "end" => date('Y-m-d')." 11:00", "notice" => "Test", "project" => $project_id];
        $response5 = $this->runApp('POST', $this->getURISheetsSave($project_hash), array_merge($data2, $csrf3));
        $this->assertEquals(301, $response5->getStatusCode());
        $this->assertEquals($this->getURIView($project_hash), $response5->getHeaderLine("Location"));

        // Get CSRF From Project overview
        $response6 = $this->runApp('GET', $this->uri_overview);
        $csrf4 = $this->extractJSCSRF($response6);

        // Delete Project
        $response = $this->runApp('DELETE', $this->uri_delete . $project_id, $csrf4);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
