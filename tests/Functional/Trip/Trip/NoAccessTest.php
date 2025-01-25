<?php

namespace Tests\Functional\Trip\Trip;

use Tests\Functional\Trip\TripTestBase;

class NoAccessTest extends TripTestBase {

    protected $TEST_TRIP_ID = 1;
    protected $TEST_TRIP_HASH = "ABCabc123";
    protected $TEST_TRIP_EVENT_ID = 1;

    protected function setUp(): void {
        $this->login("user2", "user2");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testGetParentInList() {

        $response = $this->request('GET', $this->uri_overview);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // search for all elements
        $matches = $this->getParents($body);
        foreach ($matches as $match) {
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_TRIP_HASH) {
                $this->fail("Hash found");
            }
        }
    }

    /** 
     * Edit trip
     */
    public function testGetParentEdit() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_TRIP_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());

        //$body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }



    public function testPostParentSave() {
        $data = [
            "id" => $this->TEST_TRIP_ID,
            "hash" => $this->TEST_TRIP_HASH,
            "name" => "Testtrip Update",
            "users" => [1]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_TRIP_ID, $data);

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete
     */
    public function testDeleteParent() {

        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_TRIP_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /** 
     * View Trip
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_TRIP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }
}
