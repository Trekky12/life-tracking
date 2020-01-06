<?php

namespace Tests\Functional\Trip;

class TripMemberTest extends TripTestBase {

    public function testCreateParent() {

        $this->login("admin", "admin");

        $response1 = $this->runApp('GET', $this->uri_edit);
        $csrf_data = $this->extractFormCSRF($response1);

        $data = ["name" => "Testproject No Access To user2 (member only)", "users" => [10, 11]];
        $response2 = $this->runApp('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(301, $response2->getStatusCode());
        $this->assertEquals($this->uri_overview, $response2->getHeaderLine("Location"));


        $response3 = $this->runApp('GET', $this->uri_overview);
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

        $response = $this->runApp('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = $this->getParents($body);
        $hashs = array_map(function($match) {
            return $match["hash"];
        }, $matches);
        $this->assertContains($result_data["hash"], $hashs);

        $this->logout();
    }

    /**
     * Edit trip
     * @depends testCreateParent
     */
    public function testGetParentEdit(array $result_data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->uri_edit . $result_data["id"]);

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

        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testtrip Update", "users" => [1, 10]];
        $response = $this->runApp('POST', $this->uri_save . $result_data["id"], array_merge($data, $result_data["csrf"][0]));

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

        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][1]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);

        $this->logout();
    }

    /**
     * View trip (members can access)
     * @depends testCreateParent
     */
    public function testGetViewParent(array $result_data) {
        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div id=\"trip-map\"></div>", $body);

        $this->logout();
    }

    /**
     * Events
     */

    /**
     * Add new event
     * @depends testCreateParent
     */
    public function testGetChildEdit($result) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIChildEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" action=\"" . $this->getURIChildSave($result["hash"]) . "\" method=\"POST\">", $body);

        $this->logout();

        return $this->extractFormCSRF($response);
    }

    /**
     * Create the event
     * @depends testCreateParent
     * @depends testGetChildEdit
     */
    public function testPostChildSave(array $result, array $csrf_data) {

        $this->login("user", "user");

        $data = ["name" => "Test", "trip" => $result["id"]];
        $response = $this->runApp('POST', $this->getURIChildSave($result["hash"]), array_merge($data, $csrf_data));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result["hash"]), $response->getHeaderLine("Location"));
        $this->logout();

        return $data;
    }

    /**
     * Is the created event
     * @depends testCreateParent
     * @depends testPostChildSave
     */
    public function testGetChildCreated(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data["name"]);

        $this->assertArrayHasKey("id", $row);

        $result = [];
        $result["id"] = $row["id"];
        $result["csrf"] = $this->extractJSCSRF($response);

        $this->logout();

        return $result;
    }

    /**
     * Update Event
     */

    /**
     * Edit Event
     * @depends testCreateParent
     * @depends testGetChildCreated
     */
    public function testGetChildCreatedEdit(array $result_data_parent, array $result_data_child) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIChildEdit($result_data_parent["hash"]) . $result_data_child["id"]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\"  type=\"hidden\" value=\"" . $result_data_child["id"] . "\">", $body);
        $this->assertStringContainsString("<input name=\"trip\" type=\"hidden\" value=\"" . $result_data_parent["id"] . "\">", $body);

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

        $data = ["id" => $result_data_child["id"], "trip" => $result_data_parent["id"], "name" => "Testevent updated"];
        $response = $this->runApp('POST', $this->getURIChildSave($result_data_parent["hash"]) . $result_data_child["id"], array_merge($data, $result_data_child["csrf"]));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($result_data_parent["hash"]), $response->getHeaderLine("Location"));

        $this->logout();

        return $data;
    }

    /**
     * Is the event data updated?
     * @depends testCreateParent
     * @depends testPostChildCreatedSave
     */
    public function testGetChildUpdated(array $result_data, array $data) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data["name"]);

        $this->assertArrayHasKey("id", $row);

        $result = [];
        $result["id"] = $row["id"];
        $result["csrf"] = $this->extractJSCSRF($response);

        $this->logout();

        return $result;
    }

    /**
     * Delete event
     * @depends testCreateParent
     * @depends testGetChildUpdated
     */
    public function testDeleteChild(array $result_data_parent, array $result_data_child) {

        $this->login("user", "user");

        $response = $this->runApp('DELETE', $this->getURIChildDelete($result_data_parent["hash"]) . $result_data_child["id"], $result_data_child["csrf"]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

    /**
     * clean
     * @depends testCreateParent
     */
    public function testClean(array $result_data) {

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
