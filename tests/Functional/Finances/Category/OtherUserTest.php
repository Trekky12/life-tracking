<?php

namespace Tests\Functional\Finances\Category;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {
    
    protected $uri_overview = "/finances/categories/";
    protected $uri_edit = "/finances/categories/edit/";
    protected $uri_save = "/finances/categories/save/";
    protected $uri_delete = "/finances/categories/delete/";
    
    protected $TEST_FINANCE_CATEGORY_ID = 1;
    

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        
        $matches = [];
        $re = '/<tbody>\s*<\/tbody>/';
        preg_match($re, $body, $matches);

        $this->assertFalse(empty($matches));        
    }
    
     /**
     * Edit created element
     */
    public function testGetElementCreatedEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_FINANCE_CATEGORY_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
        
        return $this->extractJSCSRF($response);
    }
    
    /**
     * @depends testGetElementCreatedEdit
     */
    public function testPostElementCreatedSave($csrf) {
        
        $data = [
            "id" => $this->TEST_FINANCE_CATEGORY_ID,
            "name" => "Test Category Updated",
            "is_default" => 1
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_FINANCE_CATEGORY_ID, array_merge($data, $csrf));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }
    
    public function testDeleteElement() {

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractJSCSRF($response1);


        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_FINANCE_CATEGORY_ID, $csrf);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Element nicht gefunden", $json["error"]);
    }
    

}
