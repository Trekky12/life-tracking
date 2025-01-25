<?php

namespace Tests\Functional\Timesheet\SheetNotice;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessWithWrongParentTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_SHEET_ID = 3;

    protected $uri_child_edit = "/timesheets/HASH/sheets/notice/ID/edit/";
    protected $uri_child_save = "/timesheets/HASH/sheets/notice/ID/save/";
    protected $uri_child_data = "/timesheets/HASH/sheets/notice/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIWithHashAndID($this->uri_child_edit, $this->TEST_PROJECT_HASH, $this->TEST_SHEET_ID));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }



    public function testPostAddElement() {

        $data = [
            "notice" => "Test Notice"
        ];

        $response = $this->request('POST', $this->getURIWithHashAndID($this->uri_child_save, $this->TEST_PROJECT_HASH, $this->TEST_SHEET_ID), $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetChildData() {

        $response = $this->request('GET', $this->getURIWithHash($this->uri_child_data, $this->TEST_PROJECT_HASH) . '?id=' . $this->TEST_SHEET_ID);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("error", $json["status"]);
        $this->assertArrayHasKey("error", $json);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
