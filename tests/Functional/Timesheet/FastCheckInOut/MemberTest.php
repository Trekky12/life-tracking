<?php

namespace Tests\Functional\Timesheet\FastCheckInOut;

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

    public function testGetTimesheetsFast() {
        $response = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div class=\"grid content-xsmall\">", $body);

        return $this->extractJSCSRF($response);
    }

    /**
     * @depends testGetTimesheetsFast
     */
    public function testPostTimesheetsFastCheckin(array $token) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckin($this->TEST_PROJECT_HASH), array_merge($data, $token));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = [
            "start" => date('Y-m-d H:i'),
            "end" => null,
            "diff" => null
        ];
        return $return_data;
    }

    /**
     * Check in result
     * @depends testPostTimesheetsFastCheckin
     */
    public function testGetTimesheetsFastCheckedIn(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_delete"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * @depends testGetTimesheetsFastCheckedIn
     */
    public function testPostTimesheetsFastCheckoutAfterCheckIn(array $result_data) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), array_merge($data, $result_data["csrf"]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = [
            "start" => date('Y-m-d H:i'),
            "end" => date('Y-m-d H:i'),
            "diff" => "00:00:00"
        ];
        return $return_data;
    }

    /**
     * Check out after Check In result
     * @depends testPostTimesheetsFastCheckoutAfterCheckIn
     */
    public function testGetTimesheetsFastCheckedOutAfterCheckIn(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_delete"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * Check out without Check in
     * @depends testGetTimesheetsFastCheckedOutAfterCheckIn
     */
    public function testPostTimesheetsFastCheckoutWithoutCheckIn(array $result_data) {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), array_merge($data, $result_data["csrf"]));

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "success");

        $return_data = [
            "start" => null,
            "end" => date('Y-m-d H:i'),
            "diff" => ""
        ];
        return $return_data;
    }

    /**
     * Check out wihtout Check In result
     * @depends testPostTimesheetsFastCheckoutWithoutCheckIn
     */
    public function testGetTimesheetsFastCheckedOutWithoutCheckIn(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        $result = [];
        $result["id"] = $row["id_delete"];
        $result["csrf"] = $this->extractJSCSRF($response);

        return $result;
    }

    /**
     * clean
     * @depends testGetTimesheetsFastCheckedOutWithoutCheckIn
     */
    public function testDeleteTimesheet1(array $data_timesheet) {
        $response1 = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));
        $token = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $data_timesheet["id"], $token);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    /**
     * 
     * @depends testGetTimesheetsFastCheckedOutAfterCheckIn
     */
    public function testDeleteTimesheet2(array $data_timesheet) {
        $response1 = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));
        $token = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $data_timesheet["id"], $token);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

}
