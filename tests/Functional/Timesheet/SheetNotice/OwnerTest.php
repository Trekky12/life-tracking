<?php

namespace Tests\Functional\Timesheet\SheetNotice;

use Tests\Functional\Timesheet\TimesheetTestBase;

class OwnerTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_SHEET_ID = 1;
    protected $uri_child_edit = "/timesheets/HASH/sheets/notice/ID/edit/";
    protected $uri_child_save = "/timesheets/HASH/sheets/notice/ID/save/";
    protected $uri_child_data = "/timesheets/HASH/sheets/notice/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIWithHashAndID($this->uri_child_edit, $this->TEST_PROJECT_HASH, $this->TEST_SHEET_ID));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form id="timesheetNoticeForm" class="form-horizontal" action="' . $this->getURIWithHashAndID($this->uri_child_save, $this->TEST_PROJECT_HASH, $this->TEST_SHEET_ID) . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "notice" => "Test Notice",
            "notice2" => "Test notice"
        ];

        $response = $this->request('POST', $this->getURIWithHashAndID($this->uri_child_save, $this->TEST_PROJECT_HASH, $this->TEST_SHEET_ID), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testGetChildData($data) {

        $response = $this->request('GET', $this->getURIWithHash($this->uri_child_data, $this->TEST_PROJECT_HASH).'?sheet='. $this->TEST_SHEET_ID);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        $this->assertArrayHasKey("entry", $json);
        $this->assertIsArray($json["entry"]);

        $this->assertSame($this->TEST_SHEET_ID, intval($json["entry"]["sheet"]));
        $this->assertArrayHasKey("notice", $json["entry"]);
    }

}
