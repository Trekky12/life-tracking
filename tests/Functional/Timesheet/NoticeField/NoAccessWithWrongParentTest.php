<?php

namespace Tests\Functional\Timesheet\NoticeField;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessWithWrongParentTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_NOTICEFIELD_ID = 2;
    
    protected $uri_child_edit = "/timesheets/HASH/noticefields/edit/";
    protected $uri_child_save = "/timesheets/HASH/noticefields/save/";
    protected $uri_child_delete = "/timesheets/HASH/noticefields/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }


    /**
     * Access a specific child
     */
    public function testGetChildEditID() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $this->TEST_NOTICEFIELD_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    
    /**
    * Update the specific child
     */
    public function testPostChildSaveID() {

        $data = [
            "id" => $this->TEST_NOTICEFIELD_ID,
            "name" => "Test Notice Field"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH). $this->TEST_NOTICEFIELD_ID, $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     */
    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_NOTICEFIELD_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

}
