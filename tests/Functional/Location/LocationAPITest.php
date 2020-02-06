<?php

namespace Tests\Functional\Location;

use Tests\Functional\Base\BaseTestCase;

class LocationAPITest extends BaseTestCase {

    public function testAPIWrongUser() {
        $response = $this->request('POST', '/location/record', [], ['user' => 'admin', 'pass' => '']);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
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
            "steps" => rand(0,10000)
        ];
        $response = $this->request('POST', '/location/record', $location_data, ['user' => 'admin', 'pass' => 'admin']);

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"success"}', $body);
        
        return $location_data;
    }
    
    /**
     * @depends testAPI
     */
    public function testgetMarkers($location_data) {
        $this->login("admin", "admin");
        
        $response = $this->request('GET', '/location/markers');
        
        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);
        
        $found = false;
        foreach($json as $marker){
            if($marker["lat"] == 52.520007 && $marker["lng"] == 13.404954 && $marker["acc"] == $location_data["gps_acc"] && $marker["steps"] == $location_data["steps"]){
                $found = true;
            }
        }
        
        if(!$found){
            $this->fail("Marker not found!");
        }
        
        $this->logout();
    }
    
    /**
     * @depends testAPI
     */
    public function testNoAccessToMarkersOfAnotherUser($location_data){
        $this->login("user", "user");
        
        $response = $this->request('GET', '/location/markers');
        
        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);
        
        $found = false;
        foreach($json as $marker){
            if($marker["lat"] == 52.520007 && $marker["lng"] == 13.404954 && $marker["acc"] == $location_data["gps_acc"] && $marker["steps"] == $location_data["steps"]){
                $found = true;
            }
        }
        
        if($found){
            $this->fail("Marker of admin found!");
        }
        
        $this->logout();
    }

}
