<?php

namespace Tests\Functional\Timesheet;

class ProjectMemberTest extends ProjectTestBase {

    public function testCreateParent() {

        $this->login("admin", "admin");

        $response1 = $this->request('GET', $this->uri_edit);
        $csrf_data = $this->extractFormCSRF($response1);

        $data = ["name" => "Testproject No Access To user2 (member only)", "users" => [10, 11]];
        $response2 = $this->request('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response2->getStatusCode());
        $this->assertEquals($this->uri_overview, $response2->getHeaderLine("Location"));


        $response3 = $this->request('GET', $this->uri_overview);
        $body = (string) $response3->getBody();

        $row = $this->getParent($body, $data["name"]);

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
     * @depends testCreateParent
     */
    public function testGetParentInList($result_data) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->uri_overview);

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
     * @depends testCreateParent
     */
    public function testGetParentEdit(array $result_data) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->uri_edit . $result_data["id"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * 
     * @depends testCreateParent
     */
    public function testPostParentSave(array $result_data) {

        $this->login("user", "user");

        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testproject Update", "users" => [1, 10]];
        $response = $this->request('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"][0]));

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * Delete
     * @depends testCreateParent
     */
    public function testDeleteParent(array $result_data) {

        $this->login("user", "user");

        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][1]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);

        $this->logout();
    }

    /**
     * View Project (members can access)
     * @depends testCreateParent
     */
    public function testGetViewParent(array $result_data) {
        $this->login("user", "user");

        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

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
     * @depends testCreateParent
     */
    public function testGetChildEdit($result) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->getURIChildEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURIChildSave($result["hash"]) . "\" method=\"POST\">", $body);

        $this->logout();

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the sheet
     * @depends testCreateParent
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $result, array $csrf_data) {

        $this->login("user", "user");

        $data = ["start" => date('Y-m-d') . " 12:00", "end" => date('Y-m-d') . " 14:10", "notice" => "Test", "project" => $result["id"]];
        $response = $this->request('POST', $this->getURIChildSave($result["hash"]), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "02:10:00";

        $this->logout();

        return $data;
    }

    /**
     * Is the created sheet now in the table?
     * @depends testCreateParent
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

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
     * @depends testCreateParent
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(array $result_data_parent, array $result_data_child) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->getURIChildEdit($result_data_parent["hash"]) . $result_data_child["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_child["id"] . "\">", $body);
        $this->assertStringContainsString("<input name=\"project\" type=\"hidden\" value=\"" . $result_data_parent["id"] . "\">", $body);

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
     * @depends testCreateParent
     * @depends testGetChildCreatedEdit
     */
    public function testPostChildCreatedSave(array $result_data_parent, array $result_data_child) {

        $this->login("user", "user");

        $data = ["id" => $result_data_child["id"], "project" => $result_data_parent["id"], "start" => date('Y-m-d') . " 10:02", "end" => date('Y-m-d') . " 18:55", "notice" => "Testnotice"];
        $response = $this->request('POST', $this->getURIChildSave($result_data_parent["hash"]) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result_data_parent["hash"]), $response->getHeaderLine("Location"));

        $data["diff"] = "08:53:00";

        $this->logout();

        return $data;
    }

    /**
     * Is the sheet data updated in the table?
     * @depends testCreateParent
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

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
     * @depends testCreateParent
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(array $result_data_parent, array $result_data_child) {

        $this->login("user", "user");

        $response = $this->request('DELETE', $this->getURIChildDelete($result_data_parent["hash"]) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

    /**
     * Test Exporting 
     * @depends testCreateParent
     */
    public function testTimesheetsSheetsExport(array $result_data_parent) {
        $this->login("user", "user");
        
        $response = $this->request('GET', $this->getURISheetsExport($result_data_parent["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $response->getHeaderLine("Content-Type"));
        $this->assertEquals("attachment; filename=\"" . date('Y-m-d') . "_Export.xlsx\"", $response->getHeaderLine("Content-Disposition"));
        $this->assertEquals("max-age=0", $response->getHeaderLine("Cache-Control"));
        
        $this->logout();
    }

    /**
     * clean
     * @depends testCreateParent
     */
    public function testClean(array $result_data) {

        $this->login("admin", "admin");

        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][2]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

}
