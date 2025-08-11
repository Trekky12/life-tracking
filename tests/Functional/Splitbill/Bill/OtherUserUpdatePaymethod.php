<?php

namespace Tests\Functional\Splitbill\Bill;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Splitbill\SplitbillTestBase;

class OtherUserUpdatePaymethod extends SplitbillTestBase {

    protected $TEST_GROUP_ID = 1;
    protected $TEST_GROUP_HASH = "ABCabc123";
    protected $TEST_BILL_ID = 1;

    protected function setUp(): void {
        $this->login("user", "user");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    /** 
     * Add new Bill
     */
    public function testGetChildEdit() {

        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<form class=\"form-horizontal\" id=\"splitbillsBillsForm\" action=\"" . $this->getURIChildSave($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID . "\" method=\"POST\">", $body);
    }

    /** 
     * Create the Bill
     */
    public function testPostChildSave() {

        $data = [
            "name" => "Test Bill Update",
            "value" => 50,
            "balance" => [
                1 => [
                    "paid" => "50.00",
                    "spend" => "0.00",
                    "paymethod" => null
                ],
                2 => [
                    "paid" => "0.00",
                    "spend" => "50.00",
                    "paymethod" => 2
                ]
            ],
            "notice" => "Test"
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));

        return $data;
    }

    #[Depends('testPostChildSave')]
    public function testChanges($data) {
        $response = $this->request('GET', $this->getURIChildEdit($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);

        $body = (string) $response->getBody();
        $input_fields = $this->getInputFields($body);
        foreach ($data as $key => $val) {
            if (strcmp($key, "balance") === 0) {
                $this->assertArrayHasKey($key, $input_fields, $key . " is missing!");
                foreach ($data[$key] as $user_id => $balance) {
                    $this->assertEquals($input_fields[$key][$user_id]["paymethod"], $balance["paymethod"], "Field: " . $key . "");
                }
            } else {
                $this->assertArrayNotHasKey($key, $input_fields, $key . " is there!");
            }
        }
    }

    public function testPostChildSaveRestore() {

        $data = [
            "balance" => [
                1 => [
                    "paymethod" => null
                ],
                2 => [
                    "paymethod" => null
                ]
            ]
        ];
        $response = $this->request('POST', $this->getURIChildSave($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID, $data);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($this->getURIView($this->TEST_GROUP_HASH), $response->getHeaderLine("Location"));
    }

    /** 
     * Delete Bill
     */
    public function testDeleteChild() {

        $response = $this->request('DELETE', $this->getURIChildDelete($this->TEST_GROUP_HASH) . $this->TEST_BILL_ID);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("Kein Zugriff erlaubt", $body);
    }
}
