<?php
declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class BaseTest extends AppTestCase {
    public function testRunApp() {
        $app = $this->getTestApp();
        $responseRoot = $app->handle($this->createRequest('/'));
        $this->assertEquals(301, $responseRoot->getStatusCode());

        $app = $this->getTestApp();
        $responseSpecific = $app->handle($this->createRequest('/v2/kissj/login'));
        $this->assertEquals(200, $responseSpecific->getStatusCode());
        
        // TODO fix
        $app = $this->getTestApp();
        $responseSpecific = $app->handle($this->createRequest('/nonexistentRoute'));
        $this->assertEquals(404, $responseSpecific->getStatusCode());
    }
}
