<?php

namespace Tests\Functional\Crawler\Dataset\Save;

use Tests\Functional\Crawler\CrawlerTestBase;

class NoAccessTest extends CrawlerTestBase {

    protected $TEST_CRAWLER_HASH = "ABCabc123";

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testDatasetSave() {
        $response = $this->request('POST', $this->getURIDatasetSave($this->TEST_CRAWLER_HASH));

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }

}
