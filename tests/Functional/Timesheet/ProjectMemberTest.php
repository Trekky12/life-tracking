<?php

namespace Tests\Functional\Timesheet;

class ProjectMemberTest extends ProjectTestBase {

    public function testProjectCreationUser1() {

        $this->login("admin", "admin");

        $response1 = $this->runApp('GET', $this->uri_edit);
        $csrf_data = $this->extractFormCSRF($response1);

        $data = ["name" => "Testproject No Access To user2 (member only)", "users" => [10, 11]];
        $response2 = $this->runApp('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response2->getStatusCode());
        $this->assertEquals($this->uri_overview, $response2->getHeaderLine("Location"));


        $response3 = $this->runApp('GET', $this->uri_overview);
        $body = (string) $response3->getBody();

        $row = $this->getTableRowProjects($body, $data["name"]);

        $this->assertArrayHasKey("hash", $row);
        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["hash"] = $row["hash"];
        $result["id"] = $row["id_edit"];

        // get multiple tokens
        $csrf = $this->extractJSCSRF($response3);
        $result["csrf"] = $this->getCSRFTokens($csrf);

        $this->logout();

        return $result;
    }

    /**
     * @depends testProjectCreationUser1
     */
    public function testProjectInListNoAccess($result_data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = [];
        $re = '/<tr data-id="(?<id>[0-9]*)">\s*<td data-title="Name"><a href="\/timesheets\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*<td>\s*<a href="(?<edit>.*)"><span class="fa fa-pencil-square-o fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="(?<delete>.*)" class="btn-delete"><span class="fa fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $result_data["hash"]) {
                $this->fail("Hash found");
            }
        }

        $this->logout();
    }

    /**
     * Edit project
     * @depends testProjectCreationUser1
     */
    public function testProjectEditViewNoAccess(array $result_data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * 
     * @depends testProjectCreationUser1
     */
    public function testPostEditProjectNoAccess(array $result_data) {

        $this->login("user", "user");

        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testproject Update", "users" => [1, 10]];
        $response = $this->runApp('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"][0]));

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * Delete
     * @depends testProjectCreationUser1
     */
    public function testDeleteProjectNoAccess(array $result_data) {

        $this->login("user", "user");

        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][1]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);

        $this->logout();
    }

    /**
     * View Project (members can access)
     * @depends testProjectCreationUser1
     */
    public function testViewProject(array $result_data) {
        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("timesheets_sheets_table", $body);

        $this->logout();
    }

    /**
     * Timesheets
     */

    /**
     * Add new Sheet
     * @depends testProjectCreationUser1
     */
    public function testGetAddSheet($result) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURISheetsEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURISheetsSave($result["hash"]) . "\" method=\"POST\">", $body);

        $this->logout();

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the sheet
     * @depends testProjectCreationUser1
     * @depends testGetAddSheet
     */
    public function testPostAddSheet(array $result, array $csrf_data) {

        $this->login("user", "user");

        $data = ["start" => date('Y-m-d') . " 12:00", "end" => date('Y-m-d') . " 14:10", "notice" => "Test", "project" => $result["id"]];
        $response = $this->runApp('POST', $this->getURISheetsSave($result["hash"]), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "02:10:00";

        $this->logout();

        return $data;
    }

    /**
     * Is the created sheet now in the table?
     * @depends testProjectCreationUser1
     * @depends testPostAddSheet
     */
    public function testGetCreatedSheet(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getTableRowSheets($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        $this->logout();

        return $result;
    }

    /**
     * Update sheet
     */

    /**
     * Edit Sheet
     * @depends testProjectCreationUser1
     * @depends testGetCreatedSheet
     */
    public function testGetSheetEdit(array $result_data_project, array $result_data_sheet) {

        $this->login("user", "user");

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

        $this->logout();

        return $result;
    }

    /**
     * 
     * @depends testProjectCreationUser1
     * @depends testGetSheetEdit
     */
    public function testPostSheetEdit(array $result_data_project, array $result_data_sheet) {

        $this->login("user", "user");

        $data = ["id" => $result_data_sheet["id"], "project" => $result_data_project["id"], "start" => date('Y-m-d') . " 10:02", "end" => date('Y-m-d') . " 18:55", "notice" => "Testnotice"];
        $response = $this->runApp('POST', $this->getURISheetsSave($result_data_project["hash"]) . $result_data_sheet["id"], array_merge($data, $result_data_sheet["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result_data_project["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "08:53:00";

        $this->logout();

        return $data;
    }

    /**
     * Is the sheet data updated in the table?
     * @depends testProjectCreationUser1
     * @depends testPostSheetEdit
     */
    public function testGetSheetUpdated(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getTableRowSheets($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        $this->logout();

        return $result;
    }

    /**
     * Delete sheet
     * @depends testProjectCreationUser1
     * @depends testGetSheetUpdated
     */
    public function testDeleteSheet(array $result_data_project, array $result_data_sheet) {

        $this->login("user", "user");

        $response = $this->runApp('DELETE', $this->getURISheetsDelete($result_data_project["hash"]) . $result_data_sheet["id"], $result_data_sheet["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

    /**
     * Test Exporting 
     * @depends testProjectCreationUser1
     */
    public function testTimesheetsSheetsExport(array $result_data_project) {
        $this->login("user", "user");
        
        $response = $this->runApp('GET', $this->getURISheetsExport($result_data_project["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.xlsx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
        
        $this->logout();
    }

    /**
     * clean
     * @depends testProjectCreationUser1
     */
    public function testDeleteProjectOwner(array $result_data) {

        $this->login("admin", "admin");

        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][2]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

}
