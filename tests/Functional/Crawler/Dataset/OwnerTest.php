<?php

namespace Tests\Functional\Crawler\Dataset;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected $uri_child_record = "/api/crawlers/record";

    protected function setUp(): void {
        
    }

    protected function tearDown(): void {
        
    }

    public function testList() {
        $this->login("admin", "admin");
        
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="crawlers_data_table"', $body);
        
        $this->logout();
    }

    public function testPostAddElement() {

        $data = [
            'data' => [
                'title' => 'Dataset 1 Test',
                'link' => 'http://localhost',
                'value' => 1
            ],
            'identifier' => 'dataset' . rand(0, 1000),
            'crawler' => $this->TEST_CRAWLER_HASH
        ];

        $response = $this->request('POST', $this->uri_child_record, $data, ['user' => 'admin', 'pass' => 'application']);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $this->login("admin", "admin");
        
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data);

        $this->assertArrayHasKey("date", $row);
        
        $this->logout();
    }

    /**
     * 
     * @depends testPostAddElement
     * @depends testAddedElement
     */
    public function testPostElementUpdate(array $initial_data) {

        $data = [
            'data' => [
                'title' => 'Dataset 1 Updated',
                'link' => 'http://localhost/2',
                'value' => 2
            ],
            'identifier' => $initial_data['identifier'],
            'crawler' => $this->TEST_CRAWLER_HASH
        ];

        $response = $this->request('POST', $this->uri_child_record, $data, ['user' => 'admin', 'pass' => 'application']);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * Is the element updated?
     * @depends testPostElementUpdate
     */
    public function testGetElementUpdated(array $result_data) {
        $this->login("admin", "admin");
        
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("date", $row);
        
        $this->logout();
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td><span class="save_crawler_dataset " data-id="([0-9]*)">.*?<\/td>\s*<td>(?<date>[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td><a href="' . str_replace('/', "\/", $data["data"]["link"]) . '" target="_blank">' . preg_quote($data["data"]["title"]) . '<\/a><\/td>\s*<td>' . preg_quote($data["data"]["value"]) . '<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);
        
        return $matches;
    }

}
