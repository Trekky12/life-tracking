<?php

namespace Tests\Functional\Workouts\Bodypart;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/bodyparts/";
    protected $uri_edit = "/workouts/bodyparts/edit/";
    protected $uri_save = "/workouts/bodyparts/save/";
    protected $uri_delete = "/workouts/bodyparts/delete/";
    
    protected $TEST_BODYPART = 1;

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
            "name" => "Test Bodypart 1"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_BODYPART,
            "name" => "Test Bodypart 1 Update"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_BODYPART, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_BODYPART);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
