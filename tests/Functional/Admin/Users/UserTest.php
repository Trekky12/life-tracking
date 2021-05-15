<?php

namespace Tests\Functional\Admin\Users;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/users/";
    protected $uri_edit = "/users/edit/";
    protected $uri_save = "/users/save/";
    protected $uri_delete = "/users/delete/";
    
    protected $TEST_USER = 1;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testGetAddElement() {
        $response = $this->request('GET', $this->uri_edit);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    /**
     * 
     */
    public function testPostAddElement() {

        $data = [
            "login" => "a_test",
            "name" => "Erika",
            "lastname" => "Mustermann",
            "mail" => "test@localhost",
            "role" => "user",
            "password" => "test",
            "module_location" => 1,
            "module_finance" => 1,
            "module_cars" => 1,
            "module_boards" => 1,
            "module_crawlers" => 1,
            "module_splitbills" => 1,
            "module_trips" => 1,
            "module_timesheets" => 1,
            "module_workouts" => 1,
            "module_recipes" => 1,
            "force_pw_change" => 1,
            "start_url" => "/test"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_USER,
            "login" => "a_test",
            "name" => "Erika",
            "lastname" => "Mustermann",
            "mail" => "test1@localhost",
            "role" => "admin",
            "password" => "",
            "module_location" => 1,
            "module_finance" => 0,
            "module_cars" => 0,
            "module_boards" => 0,
            "module_crawlers" => 0,
            "module_splitbills" => 0,
            "module_trips" => 0,
            "module_timesheets" => 0,
            "force_pw_change" => 0,
            "start_url" => "/test1"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_USER, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_USER);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
