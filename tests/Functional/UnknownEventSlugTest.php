<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class UnknownEventSlugTest extends AppTestCase
{
    public function testUnknownSlugOnLoginRedirectsToEventList(): void
    {
        $response = $this->getTestApp()->handle($this->createRequest('/v2/event/no-such-event/login'));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringEndsWith('/v2/kissj/events', $response->getHeaderLine('Location'));
    }

    public function testUnknownSlugOnAdminRouteRedirectsToEventList(): void
    {
        $response = $this->getTestApp()->handle($this->createRequest('/v2/event/no-such-event/admin/1/mend'));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringEndsWith('/v2/kissj/events', $response->getHeaderLine('Location'));
    }

    public function testKnownSlugLoginStillRenders(): void
    {
        $response = $this->getTestApp()->handle($this->createRequest('/v2/event/test-event-slug/login'));

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRouteWithoutSlugIsNotRedirected(): void
    {
        $response = $this->getTestApp()->handle($this->createRequest('/v2/kissj/events'));

        self::assertSame(200, $response->getStatusCode());
    }
}
