<?php

namespace Tests\Functional\Location;

use PHPUnit\Framework\Attributes\Depends;
use Tests\Functional\Base\BaseTestCase;

class LocationAPITest extends BaseTestCase {

    public function testAPIWrongPassword() {
        $response = $this->request('POST', '/api/location/record', [], ['user' => 'admin', 'pass' => 'test']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString("HTTP Auth failed!", (string) $response->getBody());
    }

    public function testAPI() {

        $location_data = [
            "identifier" => "my_device",
            "device" => "",
            "date" => "",
            "time" => "",
            "batt" => 100,
            "times" => 0,
            "wifi_state" => "on",
            "gps_state" => "on",
            "mfield" => 0,
            "screen_state" => "off",
            "ups" => 0,
            "gps_loc" => "52.520007,13.404954",
            "gps_acc" => "1000",
            "gps_alt" => "",
            "gps_alt_acc" => "",
            "gps_spd" => "",
            "gps_spd_acc" => "",
            "gps_bearing" => "",
            "gps_bearing_acc" => "",
            "gps_tms" => "",
            "cell_id" => "",
            "cell_sig" => "",
            "cell_srv" => "",
            "steps" => rand(0, 10000)
        ];
        $response = $this->request('POST', '/api/location/record', $location_data, ['user' => 'admin', 'pass' => 'application']);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey("status", $json);
        $this->assertStringContainsString($json["status"], "success");

        return $location_data;
    }

    #[Depends('testAPI')]
    public function testgetMarkers($location_data) {
        $this->login("admin", "admin");

        $response = $this->request('GET', '/location/markers');

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);

        $found = false;
        foreach ($json["markers"] as $marker) {
            if ($marker["lat"] == 52.520007 && $marker["lng"] == 13.404954 && $marker["acc"] == $location_data["gps_acc"] && $marker["steps"] == $location_data["steps"]) {
                $found = true;
            }
        }

        if (!$found) {
            $this->fail("Marker not found!");
        }

        $this->logout();
    }

    #[Depends('testAPI')]
    public function testNoAccessToMarkersOfAnotherUser($location_data) {
        $this->login("user", "user");

        $response = $this->request('GET', '/location/markers');

        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);

        $found = false;
        foreach ($json["markers"] as $marker) {
            if ($marker["lat"] == 52.520007 && $marker["lng"] == 13.404954 && $marker["acc"] == $location_data["gps_acc"] && $marker["steps"] == $location_data["steps"]) {
                $found = true;
            }
        }

        if ($found) {
            $this->fail("Marker of admin found!");
        }

        $this->logout();
    }
}
