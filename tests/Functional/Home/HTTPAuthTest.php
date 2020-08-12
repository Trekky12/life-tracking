<?php

namespace Tests\Functional\API;

use Tests\Functional\Base\BaseTestCase;

class HTTPAuthTest extends BaseTestCase {

    public function testHTTPAuthNoApiRoute() {

        $response = $this->request('GET', '/', [], ['user' => 'admin', 'pass' => 'application']);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("/login", $response->getHeaderLine("Location"));
    }

}
