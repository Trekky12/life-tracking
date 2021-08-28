<?php

namespace Tests\Functional\Profile;

use Tests\Functional\Base\BaseTestCase;

class NotificationSettingsUserTest extends BaseTestCase {

    protected $uri_overview = "/notifications/manage/";
    protected $uri_set_category_user = "/notifications/setCategoryUser";
    protected $TEST_USER_CATEGORY = "2_1";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testList() {
        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<h2>Benachrichtigungen</h2>', $body);
        $this->assertStringContainsString('<h2>Push Benachrichtigungen</h2>', $body);
    }

    public function testSetUserCategory() {

        $data = [
            "category" => $this->TEST_USER_CATEGORY,
            "type" => 1
        ];
        $response = $this->request('POST', $this->uri_set_category_user, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    /**
     * @depends testSetUserCategory
     */
    public function testSettedUserCategory() {
        $response = $this->request('GET', $this->uri_overview);
        $body = (string) $response->getBody();

        $input_fields = $this->getInputFields($body);

        $this->assertArrayHasKey("user_categories", $input_fields);
        $this->assertArrayHasKey($this->TEST_USER_CATEGORY, $input_fields["user_categories"]);
        $this->assertEquals(1, $input_fields["user_categories"][$this->TEST_USER_CATEGORY]);
    }

    public function testSetUserCategoryBack() {

        $data = [
            "category" => $this->TEST_USER_CATEGORY,
            "type" => 0
        ];
        $response = $this->request('POST', $this->uri_set_category_user, $data);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        $this->assertArrayHasKey("status", $json);
        $this->assertSame("success", $json["status"]);
    }

    /**
     * @depends testSetUserCategoryBack
     */
    public function testSettedUserCategoryBack() {
        $response = $this->request('GET', $this->uri_overview);
        $body = (string) $response->getBody();

        $input_fields = $this->getInputFields($body);

        $this->assertArrayHasKey("user_categories", $input_fields);
        $this->assertArrayHasKey($this->TEST_USER_CATEGORY, $input_fields["user_categories"]);
        $this->assertSame(0, $input_fields["user_categories"][$this->TEST_USER_CATEGORY]);
    }

}
