<?php

namespace Tests\Functional\Workouts\Exercise;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/workouts/exercises/manage/";
    protected $uri_edit = "/workouts/exercises/manage/edit/";
    protected $uri_save = "/workouts/exercises/manage/save/";
    protected $uri_delete = "/workouts/exercises/manage/delete/";
    
    protected $TEST_EXERCISE = 1;

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
            "name" => "Test Exercise Insert",
            "instructions" => "Instructinos\ntest",
            "level" => 1,
            "rating" => 2,
            "category" => 1,
            "mainMuscle" => 1,
            "muscle_groups_primary" => [2, 3],
            "muscle_groups_secondary" => [4, 5],
            "mainBodyPart" => 1,
        ];

        $filename = 'exercise_image.png';

        $files = [
            [
                'name' => 'image',
                'contents' => __DIR__ . DIRECTORY_SEPARATOR . $filename,
                'filename' => $filename
            ],
            [
                'name' => 'thumbnail',
                'contents' => __DIR__ . DIRECTORY_SEPARATOR . $filename,
                'filename' => $filename
            ]
        ];

        $response = $this->request('POST', $this->uri_save, $data, [], $files);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_EXERCISE,
            "name" => "Test Exercise Update",
            "instructions" => "Instructinos\ntest 2",
            "level" => 2,
            "rating" => 3,
            "category" => 0,
            "mainMuscle" => 1,
            "muscle_groups_primary" => [],
            "muscle_groups_secondary" => [],
            "mainBodyPart" => 1,
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_EXERCISE, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_EXERCISE);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
