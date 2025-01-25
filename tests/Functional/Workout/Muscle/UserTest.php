<?php

namespace Tests\Functional\Workout\Muscle;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/muscles/";
    protected $uri_edit = "/workouts/muscles/edit/";
    protected $uri_save = "/workouts/muscles/save/";
    protected $uri_delete = "/workouts/muscles/delete/";

    protected $TEST_MUSCLE = 1;

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



    public function testPostAddElement() {

        $data = [
            "name" => "Test Muscle 1"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


    public function testPostElementCreatedSave() {

        $data = [
            "id" => $this->TEST_MUSCLE,
            "name" => "Test Muscle 1 Update"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_MUSCLE, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_MUSCLE);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
}
