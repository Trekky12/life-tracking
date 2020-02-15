<?php

namespace Tests\Functional\Splitbill\Group;

use Tests\Functional\Splitbill\SplitbillTestBase;

class OwnerDeleteRecursiveTest extends SplitbillTestBase {

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /**
     * Delete parent with childs
     */
    public function testDeleteParentWithChilds() {

        // Add Parent
        $data1 = ["name" => "Test delete with childs", "users" => [1]];
        $this->request('POST', $this->uri_save, $data1);

        // get Hash/ID from Overview
        $response3 = $this->request('GET', $this->uri_overview);
        $row = $this->getParent((string) $response3->getBody(), $data1["name"]);

        $parent_hash = $row["hash"];
        $parent_id = $row["id_edit"];

        // Add Child
        $data2 = ["name" => "Test Child", "sbgroup" => $parent_id];
        $response5 = $this->request('POST', $this->getURIChildSave($parent_hash), $data2);
        $this->assertEquals(301, $response5->getStatusCode());
        $this->assertEquals($this->getURIView($parent_hash), $response5->getHeaderLine("Location"));

        // Delete Parent
        $response = $this->request('DELETE', $this->uri_delete . $parent_id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey("is_deleted", $json);
        $this->assertTrue($json["is_deleted"]);
    }
}
