<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class HealthTest extends AppTestCase
{
    public function testHealthReturnsOk(): void
    {
        $app = $this->getTestApp();

        $response = $app->handle($this->createRequest('/v3/kissj/health'));

        self::assertSame(200, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString('{"status":"ok"}', (string)$response->getBody());
    }
}
