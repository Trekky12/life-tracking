<?php

namespace Tests\Functional\Splitbill;

class SplitbillNoAccessTest extends SplitbillTestBase {

    public function testCreateParent() {

        $this->login("admin", "admin");

        $response1 = $this->runApp('GET', $this->uri_edit);
        $csrf_data = $this->extractFormCSRF($response1);

        $data = ["name" => "Testgroup No Access (not member)", "users" => []];
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
        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $result_data["hash"]) {
                $this->fail("Hash found");
            }
        }

        $this->logout();
    }

    /**
     * Edit group
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

        $data = ["id" => $result_data["id"], "hash" => $result_data["hash"], "name" => "Testgroup Update", "users" => [10]];
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
     * View Group (member)
     * @depends testCreateParent
     */
    public function testGetViewParent(array $result_data) {
        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * View Group (owner -> has no access to view)
     * @depends testCreateParent
     */
    public function testGetViewParentOwner(array $result_data) {
        $this->login("admin", "admin");

        $response = $this->runApp('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * Bills
     */

    /**
     * Add new Bill
     * @depends testCreateParent
     */
    public function testGetChildEdit($result) {

        $this->login("user", "user");

        $response = $this->runApp('GET', $this->getURIChildEdit($result["hash"]));
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * Create the Bill
     * @depends testCreateParent
     */
    public function testPostChildSave(array $result) {

        $this->login("user", "user");

        $data = ["name" => "Test", "trip" => $result["id"]];
        $response = $this->runApp('POST', $this->getURIChildSave($result["hash"]), array_merge($data, $result["csrf"][2]));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);

        $this->logout();
    }

    /**
     * Delete Bill
     * @depends testCreateParent
     */
    public function testDeleteChild(array $result_data_parent) {

        $this->login("user", "user");

        // assume there is a sheet with ID 1
        $data_sheet_id = 1;

        $response = $this->runApp('DELETE', $this->getURIChildDelete($result_data_parent["hash"]) . $data_sheet_id, $result_data_parent["csrf"][4]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);

        $this->logout();
    }

    /**
     * clean
     * @depends testCreateParent
     */
    public function testClean(array $result_data) {

        $this->login("admin", "admin");

        $response = $this->runApp('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][5]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);

        $this->logout();
    }

}
