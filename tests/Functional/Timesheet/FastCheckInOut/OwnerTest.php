<?php

namespace Tests\Functional\Timesheet\FastCheckInOut;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Timesheet\TimesheetTestBase;

class OwnerTest extends TimesheetTestBase {

    protected $TEST_PROJECT_ID = 1;
    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetTimesheetsFast() {
        $response = $this->request('GET', $this->getURISheetsFast($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<div class=\"grid content-xsmall\">", $body);
    }



    public function testPostTimesheetsFastCheckin() {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckin($this->TEST_PROJECT_HASH), $data);

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
     */
    #[Depends('testPostTimesheetsFastCheckin')]
    public function testGetTimesheetsFastCheckedIn(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_delete"]);
    }

    #[Depends('testGetTimesheetsFastCheckedIn')]
    public function testPostTimesheetsFastCheckoutAfterCheckIn() {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), $data);

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
     */
    #[Depends('testPostTimesheetsFastCheckoutAfterCheckIn')]
    public function testGetTimesheetsFastCheckedOutAfterCheckIn(array $data) {

        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_delete"]);
    }

    /** 
     * Check out without Check in
     */
    #[Depends('testGetTimesheetsFastCheckedOutAfterCheckIn')]
    public function testPostTimesheetsFastCheckoutWithoutCheckIn() {
        $data = [];
        $response = $this->request('POST', $this->getURISheetsFastCheckout($this->TEST_PROJECT_HASH), $data);

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
     */
    #[Depends('testPostTimesheetsFastCheckoutWithoutCheckIn')]
    public function testGetTimesheetsFastCheckedOutWithoutCheckIn(array $data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getChild($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_delete"]);
    }

    /** 
     * clean
     */
    #[Depends('testGetTimesheetsFastCheckedOutWithoutCheckIn')]
    public function testDeleteTimesheet1(int $timesheet_id) {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $timesheet_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }

    #[Depends('testGetTimesheetsFastCheckedOutAfterCheckIn')]
    public function testDeleteTimesheet2(int $timesheet_id) {
        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $timesheet_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
