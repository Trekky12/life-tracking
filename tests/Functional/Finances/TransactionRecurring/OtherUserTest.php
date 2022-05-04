<?php

namespace Tests\Functional\Finances\TransactionRecurring;

use Tests\Functional\Base\BaseTestCase;

class OtherUserTest extends BaseTestCase {
    
    protected $uri_overview = "/finances/transactions/recurring/";
    protected $uri_edit = "/finances/transactions/recurring/edit/";
    protected $uri_save = "/finances/transactions/recurring/save/";
    protected $uri_delete = "/finances/transactions/recurring/delete/";
    
    protected $TEST_FINANCE_TRANSACTION_RECURRING_ID = 1;
    

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
        $response = $this->request('GET', $this->uri_edit . $this->TEST_FINANCE_TRANSACTION_RECURRING_ID);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }
    
    /**
     * 
     */
    public function testPostElementCreatedSave() {
        
        $data = [
            "id" => $this->TEST_FINANCE_TRANSACTION_RECURRING_ID,
            "description" => "Test Transaction 2 Update",
            "value" => rand(0, 10000) / 100,
            "account_from" => 1,
            "account_to" => 3,
            "start" => date('Y-m-d'),
            "end" => null,
            "unit" => "day",
            "multiplier" => 1,
            "is_active" => 1
        ];

        $response = $this->request('POST', $this->uri_save . $this->TEST_FINANCE_TRANSACTION_RECURRING_ID, $data);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Element nicht gefunden", $body);
    }
    
    public function testDeleteElement() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_FINANCE_TRANSACTION_RECURRING_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertFalse($json["is_deleted"]);
        $this->assertSame("Element nicht gefunden", $json["error"]);
    }
    

}
