<?php

namespace Tests\Functional\Crawler\Dataset;

use Tests\Functional\Crawler\CrawlerTestBase;

class MemberTest extends CrawlerTestBase {

    protected $uri_child_record = "/api/crawlers/record";

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

        $response = $this->request('POST', $this->uri_child_record, $data, ['user' => 'user', 'pass' => 'application']);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Kein Zugriff erlaubt', $body);
    }
}
