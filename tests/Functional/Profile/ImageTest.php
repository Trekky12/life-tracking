<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class ImageTest extends BaseTestCase {

    protected $uri_overview = "/profile/image";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<form action="/profile/image" method="POST" enctype="multipart/form-data">', $body);
    }

    public function testImageUpload() {

        $filename = 'profile_image';
        $file_extension = 'png';

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractFormCSRF($response1);

        $data = [
            [
                'name' => 'image',
                'contents' => __DIR__ . DIRECTORY_SEPARATOR . $filename . '.' . $file_extension,
                'filename' => $filename . '.' . $file_extension
            ]
        ];

        $response = $this->request('POST', $this->uri_overview, $csrf, [], $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
        
        return ["filename" => $filename, "extension" => $file_extension];
    }

    /**
     * @depends testImageUpload
     */
    public function testImageAvailable($data) {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertRegExp('/<img class="image_uploaded" src="\/uploads\/(.*)_' . $data["filename"] . '-small.' . $data["extension"] . '"\/>/', $body);
    }

    public function testdeleteImageUpload() {

        $response1 = $this->request('GET', $this->uri_overview);
        $csrf = $this->extractFormCSRF($response1);

        $data = array_merge(['delete_image' => 1], $csrf);

        $response = $this->request('POST', $this->uri_overview, $data, []);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->uri_overview, $response->getHeaderLine("Location"));
    }
    
    /**
     * @depends testdeleteImageUpload
     */
    public function testImageNotAvailable() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertNotRegExp('/<img class="image_uploaded" src="\/uploads\/(.*)"\/>/', $body);
    }

}
