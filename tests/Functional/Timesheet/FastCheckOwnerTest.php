<?php

namespace Tests\Functional\Timesheet;

class FastCheckOwnerTest extends ProjectTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testCreateParent() {
        $response1 = $this->request('GET', $this->uri_edit);
        $csrf_data = $this->extractFormCSRF($response1);

        $data = ["name" => "Testproject Fast CheckIn and Checkout", "users" => [10, 11]];
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

        return $result;
    }

    /**
     * @depends testCreateParent
     */
    public function testGetTimesheetsFast($result_data) {
        $response = $this->request('GET', $this->getURISheetsFast($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div class=\"grid content-xsmall\">", $body);
    }

    /**
     * @depends testCreateParent
     */
    public function testPostTimesheetsFastCheckin(array $result_data) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckin($result_data["hash"]), array_merge($data, $result_data["csrf"][0]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = ["start" => date('Y-m-d H:i'), "end" => null, "diff" => null];
        return $return_data;
    }

    /**
     * Check in result
     * @depends testCreateParent
     * @depends testPostTimesheetsFastCheckin
     */
    public function testGetTimesheetsFastCheckedIn(array $result_data, array $data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);
    }

    /**
     * @depends testCreateParent
     */
    public function testPostTimesheetsFastCheckoutAfterCheckIn(array $result_data) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($result_data["hash"]), array_merge($data, $result_data["csrf"][1]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = ["start" => date('Y-m-d H:i'), "end" => date('Y-m-d H:i'), "diff" => "00:00:00"];
        return $return_data;
    }

    /**
     * Check out after Check In result
     * @depends testCreateParent
     * @depends testPostTimesheetsFastCheckoutAfterCheckIn
     */
    public function testGetTimesheetsFastCheckedOutAfterCheckIn(array $result_data, array $data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Check out without Check in
     * @depends testCreateParent
     */
    public function testPostTimesheetsFastCheckoutWithoutCheckIn(array $result_data) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($result_data["hash"]), array_merge($data, $result_data["csrf"][2]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = ["start" => null, "end" => date('Y-m-d H:i'), "diff" => ""];
        return $return_data;
    }
    
    /**
     * Check out wihtout Check In result
     * @depends testCreateParent
     * @depends testPostTimesheetsFastCheckoutWithoutCheckIn
     */
    public function testGetTimesheetsFastCheckedOutWithoutCheckIn(array $result_data, array $data) {
        $response = $this->request('GET', $this->getURIView($result_data["hash"]));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $result_data["hash"]);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_edit"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * clean
     * @depends testCreateParent
     */
    public function testDeleteProjectOwner(array $result_data) {
        $response = $this->request('DELETE', $this->uri_delete . $result_data["id"], $result_data["csrf"][3]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
