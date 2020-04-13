<?php

namespace Tests\Functional\Crawler\Dataset\Save;

use Tests\Functional\Crawler\CrawlerTestBase;

class OwnerTest extends CrawlerTestBase {

    protected $TEST_CRAWLER_HASH = "ABCabc123";
    protected $TEST_DATASET_ID = 1;

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testDatasetWrongStateSave() {
        $response = $this->request('POST', $this->getURIDatasetSave($this->TEST_CRAWLER_HASH), ["dataset" => $this->TEST_DATASET_ID, "state" => 2]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"error"}', $body);
    }

    public function testDatasetSave() {
        $response = $this->request('POST', $this->getURIDatasetSave($this->TEST_CRAWLER_HASH), ["dataset" => $this->TEST_DATASET_ID, "state" => 1]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"success"}', $body);
    }

    public function testDatasetUnSave() {
        $response = $this->request('POST', $this->getURIDatasetSave($this->TEST_CRAWLER_HASH), ["dataset" => $this->TEST_DATASET_ID, "state" => 0]);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"success"}', $body);
    }

}
