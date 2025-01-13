<?php

namespace Tests\Functional\Timesheet\NoticeField;

use Tests\Functional\Timesheet\TimesheetTestBase;

class MemberTest extends TimesheetTestBase {
    
    protected $TEST_PROJECT_HASH = "ABCabc123";

    protected $uri_child_overview = "/timesheets/HASH/noticefields/";
    protected $uri_child_edit = "/timesheets/HASH/noticefields/edit/";
    protected $uri_child_save = "/timesheets/HASH/noticefields/save/";
    protected $uri_child_delete = "/timesheets/HASH/noticefields/delete/";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="noticefields_table"', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form class="form-horizontal" action="' . $this->getURIChildSave($this->TEST_PROJECT_HASH) . '" method="POST">', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Notice Field"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH), $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_PROJECT_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getNoticeFieldElementInTable($body, $data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * Edit created element
     * @depends testAddedElement
     * @depends testPostAddElement
     */
    public function testGetElementCreatedEdit(int $entry_id, array $data) {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $entry_id);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString("<input name=\"id\" id=\"entry_id\" type=\"hidden\" value=\"" . $entry_id . "\">", $body);

        $matches = [];
        $re = '/<form class="form-horizontal" action="(?<save>[\/a-zA-Z0-9]*)" method="POST">.*<input name="id" id="entry_id" type="hidden" value="(?<id>[0-9]*)">/s';
        preg_match($re, $body, $matches);

        $this->assertArrayHasKey("save", $matches);
        $this->assertArrayHasKey("id", $matches);

        $this->compareInputFields($body, $data);

        return intval($matches["id"]);
    }

    /**
     * 
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave(int $entry_id) {
        $data = [
            "id" => $entry_id,
            "name" => "Test Notice Field Updated"
        ];

        $response = $this->request('POST', $this->getURIChildSave($this->TEST_PROJECT_HASH) . $entry_id, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIChildOverview($this->TEST_PROJECT_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementCreatedSave
     */
    public function testGetElementUpdated(array $result_data) {
        $response = $this->request('GET', $this->getURIChildOverview($this->TEST_PROJECT_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getNoticeFieldElementInTable($body, $result_data, $this->TEST_PROJECT_HASH);

        $this->assertArrayHasKey("id_edit", $row);
        $this->assertArrayHasKey("id_delete", $row);

        return intval($row["id_edit"]);
    }

    /**
     * @depends testGetElementUpdated
     * @depends testPostElementCreatedSave
     */
    public function testChanges(int $child_id, $data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_PROJECT_HASH) . $child_id);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

    /**
     * @depends testGetElementUpdated
     */
    public function testDeleteElement(int $entry_id) {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_PROJECT_HASH) . $entry_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
    }

    protected function getURIChildOverview($hash) {
        return str_replace("HASH", $hash, $this->uri_child_overview);
    }

}
