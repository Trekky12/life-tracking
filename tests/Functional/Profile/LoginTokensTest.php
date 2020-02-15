<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class LoginTokensTest extends BaseTestCase {

    protected $uri_overview = "/profile/tokens/";
    protected $uri_delete = "/profile/tokens/delete/";


    public function testList() {
        $this->login("admin", "admin");
        
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="tokens_table"', $body);
        
        $row = $this->getElementInTable($body);
        
        $this->assertFalse(empty($row));
        $this->assertArrayHasKey("id_delete", $row);
        
        $this->logout();
    }
    
    
    public function deleteTokenTest(){
        $this->login("admin", "admin");
        
        // login and get current token id
        $response1 = $this->request('GET', $this->uri_overview);
        $body1 = (string) $response1->getBody();
        $row = $this->getElementInTable($body1);
        $id = $row["id_delete"];
        
        // delete the token
        $response = $this->request('DELETE', $this->uri_delete . $id);
        
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('{"is_deleted":true,"error":""}', $body);
        
        // check if token is deleted (force-logout)
        $response3 = $this->request('GET', $this->uri_overview);
        
        $this->assertEquals(302, $response3->getStatusCode());
        $this->assertEquals("/login", $response3->getHeaderLine("Location"));
    }

    protected function getElementInTable($body) {        
        $matches = [];
        $re = '/<tr>\s*<td>x<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td>(.?)*<\/td>\s*<td>127.0.0.1<\/td>\s*<td><a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
