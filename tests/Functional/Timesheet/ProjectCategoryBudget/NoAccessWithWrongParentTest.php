<?php

namespace Tests\Functional\Timesheet\ProjectCategoryBudget;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessWithWrongParentTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_CATEGORY_BUDGET_ID = 3;

    protected $uri_child_edit = "/timesheets/HASH/categorybudget/edit/";
    protected $uri_child_save = "/timesheets/HASH/categorybudget/save/";
    protected $uri_child_delete = "/timesheets/HASH/categorybudget/delete/";

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
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $this->TEST_CATEGORY_BUDGET_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /**
     * Update the specific child
     */
    public function testPostChildSaveID() {

        $data = [
            "id" => $this->TEST_CATEGORY_BUDGET_ID,
            "name" => "Test Category Budget Updated",
            "value" => 10
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $this->TEST_CATEGORY_BUDGET_ID, $data);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }



    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_CATEGORY_BUDGET_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }
}
