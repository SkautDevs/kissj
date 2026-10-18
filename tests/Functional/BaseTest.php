<?php

declare(strict_types=1);

namespace Tests\Functional;

use Slim\Exception\HttpNotFoundException;
use Tests\AppTestCase;

class BaseTest extends AppTestCase
{
    public function testRunApp(): void
    {
        $app = $this->getTestApp();
        $responseRoot = $app->handle($this->createRequest('/'));
        self::assertSame(301, $responseRoot->getStatusCode());

        $app = $this->getTestApp();
        $responseSpecific = $app->handle($this->createRequest('/v2/event/test-event-slug/login'));
        self::assertSame(200, $responseSpecific->getStatusCode());

        $app = $this->getTestApp();
        $this->expectException(HttpNotFoundException::class);
        $app->handle($this->createRequest('/nonexistentRoute/whichIsNotEventSlug'));
    }
}
