<?php

namespace Tests\Functional\Crawler\Dataset;

use Tests\Functional\Crawler\CrawlerTestBase;

class NoAccessTest extends CrawlerTestBase {

    protected $uri_child_record = "/crawlers/HASH/record/";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testPostAddElement() {

        $data = [
            'data' => [
                'title' => 'Dataset 1 Test',
                'link' => 'http://localhost',
                'value' => 1
            ],
            'identifier' => 'dataset' . rand(0, 1000)
        ];

        $response = $this->request('POST', $this->getURIRecord($this->TEST_CRAWLER_HASH), $data);

        $body = (string) $response->getBody();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

    protected function getURIRecord($hash) {
        return str_replace("HASH", $hash, $this->uri_child_record);
    }

}
