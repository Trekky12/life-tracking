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

        return $this->extractJSCSRF($response);
    }

    /**
     * @depends testGetAddElement
     */
    public function testPostAddElement($csrf_data) {

        $data = [
            "name" => "Test Notification Category 2",
            "identifier" => "test_notification_category2"
        ];

        $response = $this->request('POST', $this->uri_save, array_merge($data, $csrf_data));

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
    
    
    public function testPostElementCreatedSave() {
        
        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);
        
        $data = [
            "id" => $this->TEST_CATEGORY,
            "name" => "Test Notification Category 2",
            "identifier" => "test_notification_category2"
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_CATEGORY, array_merge($data, $csrf));

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    public function testDeleteElement() {

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CATEGORY, $csrf);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }


}
