<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Telemetry\Sentry\UrlRedactor;
use Tests\AppTestCase;

class SentryRouteRedactionTest extends AppTestCase
{
    public function testRedactorStillMatchesTheLoginTokenRoutePattern(): void
    {
        $pattern = $this->getTestApp()
            ->getRouteCollector()
            ->getNamedRoute('loginWithToken')
            ->getPattern();

        $url = str_replace(
            ['{eventSlug}', '{token}'],
            ['test-event-slug', '82a97187f09d55218e59bd91188262e4'],
            $pattern,
        );

        self::assertStringNotContainsString(
            '82a97187f09d55218e59bd91188262e4',
            UrlRedactor::redact($url),
            'the login token route was renamed - update the pattern in UrlRedactor::redact()',
        );
    }
}
