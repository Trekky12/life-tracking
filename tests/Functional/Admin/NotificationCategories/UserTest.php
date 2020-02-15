<?php

namespace Tests\Functional\Admin\NotificationCategories;

use Tests\Functional\Base\BaseTestCase;

class UserTest extends BaseTestCase {

    protected $uri_overview = "/notifications/categories/";
    protected $uri_edit = "/notifications/categories/edit/";
    protected $uri_save = "/notifications/categories/save/";
    protected $uri_delete = "/notifications/categories/delete/";
    
    protected $TEST_CATEGORY = 2;

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
     */
    public function testPostAddElement() {

        $data = [
            "name" => "Test Notification Category 2",
            "identifier" => "test_notification_category2"
        ];

        $response = $this->request('POST', $this->uri_save, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_CATEGORY,
            "name" => "Test Notification Category 2",
            "identifier" => "test_notification_category2"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_CATEGORY, $data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CATEGORY);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
