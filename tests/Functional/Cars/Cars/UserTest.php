<?php

namespace Tests\Functional\Cars\Cars;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/cars/control/";
    protected $uri_edit = "/cars/control/edit/";
    protected $uri_save = "/cars/control/save/";
    protected $uri_delete = "/cars/control/delete/";
    
    protected $TEST_CAR = 1;

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
            "name" => "Test Car 2",
            "users" => [1, 2],
            "mileage_per_year" => 10000,
            "mileage_term" => 4,
            "mileage_start_date" => date('Y-m-d')
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_CAR,
            "name" => "Test Car Update",
            "users" => [1, 2],
            "mileage_per_year" => 10000,
            "mileage_term" => 4,
            "mileage_start_date" => date('Y-m-d')
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_CAR, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CAR);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
