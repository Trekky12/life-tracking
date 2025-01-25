<?php

namespace Tests\Functional\Cars\Cars;

use Tests\Functional\Cars\CarTestBase;

class MemberTest extends CarTestBase {

    protected $TEST_CAR_ID = 1;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = [];
        $re = '/<tr>\s*<td>(?<name>.*)<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (array_key_exists("id_edit", $match) && $match["id_edit"] == $this->TEST_CAR_ID) {
                $this->fail("Car found");
            }
        }
    }

    /** 
     * Edit
     */
    public function testGetParentEdit() {
        $response = $this->request('GET', $this->uri_edit . $this->TEST_CAR_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }



    public function testPostParentSave() {

        $data = [
            "id" => $this->TEST_CAR_ID,
            "name" => "Test Car Updated",
            "users" => [1, 2],
            "mileage_per_year" => 15000,
            "mileage_term" => 4,
            "mileage_start_date" => date('Y-m-d')
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_CAR_ID, $data);

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete
     */
    public function testDeleteParent() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_CAR_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
