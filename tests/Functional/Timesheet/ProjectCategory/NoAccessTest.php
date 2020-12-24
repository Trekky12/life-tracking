<?php

namespace Tests\Functional\Timesheet\ProjectCategory;

use Tests\Functional\Timesheet\TimesheetTestBase;

class NoAccessTest extends TimesheetTestBase {

    protected $TEST_PROJECT_HASH = "ABCabc123";
    protected $TEST_CATEGORY_ID = 1;
    
    protected $uri_child_overview = "/timesheets/HASH/categories/";
    protected $uri_child_edit = "/timesheets/HASH/categories/edit/";
    protected $uri_child_save = "/timesheets/HASH/categories/save/";
    protected $uri_child_delete = "/timesheets/HASH/categories/delete/";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Category"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH), $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);

    }

    /**
     */
    public function testPostElementCreatedSave() {
 
        $data = [
            "id" => $this->TEST_CATEGORY_ID,
            "name" => "Test Category Updated"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $this->TEST_CATEGORY_ID, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     */
    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $this->TEST_CATEGORY_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Kein Zugriff erlaubt", $json["error"]);
    }

    protected function getURIChildOverview($hash) {
        return str_replace("HASH", $hash, $this->uri_child_overview);
    }

}
