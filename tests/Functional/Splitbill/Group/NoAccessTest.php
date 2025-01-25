<?php

namespace Tests\Functional\Splitbill\Group;

use Tests\Functional\Splitbill\SplitbillTestBase;

class NoAccessTest extends SplitbillTestBase {

    protected $TEST_GROUP_ID = 1;
    protected $TEST_GROUP_HASH = "ABCabc123";
    protected $TEST_BILL_ID = 1;

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
            if (array_key_exists("hash", $match) && $match["hash"] == $this->TEST_GROUP_HASH) {
                $this->fail("Hash found");
            }
        }
    }

    /** 
     * Edit group
     */
    public function testGetParentEdit() {

        $response = $this->request('GET', $this->uri_edit . $this->TEST_GROUP_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }



    public function testPostParentSave() {

        $data = [
            "id" => $this->TEST_GROUP_ID,
            "hash" => $this->TEST_GROUP_HASH,
            "name" => "Testgroup Update",
            "users" => [1]
        ];
        $response = $this->request('POST', $this->uri_save . $this->TEST_GROUP_ID, $data);

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * Delete
     */
    public function testDeleteParent() {
        $response = $this->request('DELETE', $this->uri_delete . $this->TEST_GROUP_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }

    /** 
     * View Group (member)
     */
    public function testGetViewParent() {
        $response = $this->request('GET', $this->getURIView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }

    /** 
     * View Group (owner -> has no access to view)
     */
    public function testGetViewParentOwner() {
        $response = $this->request('GET', $this->getURIView($this->TEST_GROUP_HASH));

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString("<p>Kein Zugriff erlaubt</p>", $body);
    }
}
