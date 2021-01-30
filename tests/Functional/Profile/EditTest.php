<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class EditTest extends BaseTestCase {

    protected $uri_overview = "/profile/edit";
    protected $uri_save = "/profile/edit";

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/edit" method="POST">', $body);
    }

    /**
     * 
     */
    public function testUpdate() {

        $data = [
            "name" => "Erika1",
            "lastname" => "Mustermann1",
            "mail" => "test1@localhost",
            "module_location" => 0,
            "module_finance" => 0,
            "module_cars" => 0,
            "module_boards" => 0,
            "module_crawlers" => 0,
            "module_splitbills" => 0,
            "module_trips" => 0,
            "module_timesheets" => 0,
            "start_url" => "/test1"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * @depends testUpdate
     */
    public function testChanges($data) {
        $response = $this->request('GET', $this->uri_overview);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }
    
    public function testUpdateBack() {

        $data = [
            "name" => "Erika",
            "lastname" => "Mustermann",
            "mail" => "test@localhost",
            "module_location" => 1,
            "module_finance" => 1,
            "module_cars" => 1,
            "module_boards" => 1,
            "module_crawlers" => 1,
            "module_splitbills" => 1,
            "module_trips" => 1,
            "module_timesheets" => 1,
            "start_url" => "/test"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));

        return $data;
    }
    
     /**
     * @depends testUpdateBack
     */
    public function testChangesBack($data) {
        $response = $this->request('GET', $this->uri_overview);

        $body = (string) $response->getBody();
        $this->compareInputFields($body, $data);
    }

}
