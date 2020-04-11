<?php

namespace Tests\Functional\Crawler\Dataset;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected $uri_child_record = "/crawlers/HASH/record/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<table id="crawlers_data_table"', $body);
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
        $this->assertEquals('{"status":"success"}', $body);

        return $data;
    }

    /**
     * @depends testPostAddElement
     */
    public function testAddedElement($data) {
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $row = $this->getElementInTable($body, $data);

        $this->assertArrayHasKey("date", $row);
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
            'identifier' => $initial_data['identifier']
        ];

        $response = $this->request('POST', $this->getURIRecord($this->TEST_CRAWLER_HASH), $data);

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
        $response = $this->request('GET', $this->getURIView($this->TEST_CRAWLER_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        $row = $this->getElementInTable($body, $result_data);

        $this->assertArrayHasKey("date", $row);
    }

    protected function getElementInTable($body, $data) {
        $matches = [];
        $re = '/<tr>\s*<td>(?<date>[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})<\/td>\s*<td><a href="' . str_replace('/', "\/", $data["data"]["link"]) . '" target="_blank">' . preg_quote($data["data"]["title"]) . '<\/a><\/td>\s*<td>' . preg_quote($data["data"]["value"]) . '<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getURIRecord($hash) {
        return str_replace("HASH", $hash, $this->uri_child_record);
    }

}
