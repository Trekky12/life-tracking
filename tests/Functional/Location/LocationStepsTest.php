<?php

namespace Tests\Functional\Location;

use Tests\Functional\Base\BaseTestCase;

class LocationStepsTest extends BaseTestCase {

    protected $uri_edit = "/location/steps/DATE/edit/";

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', '/location/steps/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="stepsSummaryChart"', $body);
        $this->assertStringContainsString('<table id="steps_table"', $body);
    }

    public function testYearOverview() {
        $year = date('Y');
        $response = $this->request('GET', '/location/steps/' . $year . '/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="stepsSummaryChart"', $body);
        $this->assertStringContainsString('<table id="steps_year_table"', $body);
    }

    public function testMonthOverview() {
        $year = date('Y');
        $month = date('m');
        $date = date('Y-m-d');
        $response = $this->request('GET', '/location/steps/' . $year . '/' . $month . '/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="stepsSummaryChart"', $body);
        $this->assertStringContainsString('<table id="steps_month_table"', $body);

        $element = $this->getElement($body, $date);

        return ["year" => $year, "month" => $month, "date" => $date, "element" => $element];
    }

    /**
     * @depends testMonthOverview
     */
    public function testGetStepsEdit($steps_data) {
        $response = $this->request('GET', '/location/steps/' . $steps_data["date"] . '/edit/');

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('<form class="form-horizontal" id="locationForm" action="/location/steps/' . $steps_data["date"] . '/save/" method="POST">', $body);

        return $this->extractFormCSRF($response);
    }

    /**
     * 
     * @depends testMonthOverview
     * @depends testGetStepsEdit
     */
    public function testPostStepsEdit($steps_data, $csrf) {
        $data = ["steps" => rand(0, 10000)];
        $response = $this->request('POST', '/location/steps/' . $steps_data["date"] . '/save/', array_merge($data, $csrf));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/location/steps/" . $steps_data["year"] . "/" . $steps_data["month"] . "/", $response->getHeaderLine("Location"));

        return $data;
    }

    /**
     * 
     * @depends testMonthOverview
     * @depends testPostStepsEdit
     */
    public function testEditedSteps($steps_data, $edited_steps_data) {

        $response = $this->request('GET', "/location/steps/" . $steps_data["year"] . "/" . $steps_data["month"] . "/");
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $element = $this->getElement($body, $steps_data["date"]);
        $new_steps = intval(filter_var($element["steps"], FILTER_SANITIZE_NUMBER_INT));
        
        if($new_steps != $edited_steps_data["steps"]){
            $this->fail("Wrong value for steps!");
        }
    }

    protected function getURIEdit($date) {
        return str_replace("DATE", $date, $this->uri_edit);
    }

    protected function getElement($body, $date) {
        $matches = [];
        $re = '/<tr>\s*<td>(?<date>' . preg_quote($date) . ')<\/td>\s*<td>(?<steps>[0-9.]+)<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIEdit($date)) . '"><i class="fas fa-edit fa-lg"><\/i><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
