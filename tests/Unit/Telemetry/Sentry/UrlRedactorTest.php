<?php

declare(strict_types=1);

namespace Tests\Unit\Telemetry\Sentry;

use kissj\Telemetry\Sentry\UrlRedactor;
use PHPUnit\Framework\TestCase;
use Sentry\Event;

class UrlRedactorTest extends TestCase
{
    public function testRedactsLoginTokenFromTryLoginPath(): void
    {
        self::assertSame(
            'http://kissj.net/v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED,
            UrlRedactor::redact('http://kissj.net/v2/event/cej26/tryLogin/82a97187f09d55218e59bd91188262e4'),
        );
    }

    public function testRedactsOtTokenWhenItIsTheOnlyQueryParameter(): void
    {
        self::assertSame(
            'http://kissj.net/v2/event/korbo26/login?ot_token=' . UrlRedactor::REDACTED,
            UrlRedactor::redact('http://kissj.net/v2/event/korbo26/login?ot_token=fishy-yale-expensive-jake'),
        );
    }

    public function testRedactsOtTokenInBareQueryStringWithoutLeadingQuestionMark(): void
    {
        self::assertSame(
            'ot_token=' . UrlRedactor::REDACTED,
            UrlRedactor::redact('ot_token=fishy-yale-expensive-jake'),
        );
    }

    public function testRedactsOtTokenWhenItIsNotTheFirstParameter(): void
    {
        self::assertSame(
            'lang=cs&ot_token=' . UrlRedactor::REDACTED . '&page=2',
            UrlRedactor::redact('lang=cs&ot_token=fishy-yale-expensive-jake&page=2'),
        );
    }

    public function testPreservesQueryStringFollowingARedactedTryLoginSegment(): void
    {
        self::assertSame(
            '/v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED . '?lang=cs',
            UrlRedactor::redact('/v2/event/cej26/tryLogin/82a97187f09d55218e59bd91188262e4?lang=cs'),
        );
    }

    public function testRedactsBothTokenKindsInOneUrl(): void
    {
        self::assertSame(
            '/v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED . '?ot_token=' . UrlRedactor::REDACTED,
            UrlRedactor::redact('/v2/event/cej26/tryLogin/abc123?ot_token=def456'),
        );
    }

    public function testLeavesUrlWithoutTokensUntouched(): void
    {
        self::assertSame(
            'http://kissj.net/v2/event/cej26/getDashboard',
            UrlRedactor::redact('http://kissj.net/v2/event/cej26/getDashboard'),
        );
    }

    public function testDoesNotMatchAQueryParameterMerelyEndingInOtToken(): void
    {
        self::assertSame(
            'not_ot_token=keepme',
            UrlRedactor::redact('not_ot_token=keepme'),
        );
    }

    public function testIsIdempotent(): void
    {
        $once = UrlRedactor::redact('/v2/event/cej26/tryLogin/abc123?ot_token=def456');

        self::assertSame($once, UrlRedactor::redact($once));
    }

    public function testScrubsRequestUrlAndQueryString(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'url' => 'http://kissj.net/v2/event/korbo26/login?ot_token=fishy-yale-expensive-jake',
            'query_string' => 'ot_token=fishy-yale-expensive-jake',
            'method' => 'GET',
        ]);

        $request = UrlRedactor::scrub($event)->getRequest();

        self::assertSame(
            'http://kissj.net/v2/event/korbo26/login?ot_token=' . UrlRedactor::REDACTED,
            $request['url'],
        );
        self::assertSame('ot_token=' . UrlRedactor::REDACTED, $request['query_string']);
        self::assertSame('GET', $request['method'], 'unrelated request keys must survive');
    }

    public function testScrubsOtTokenFromPostedRequestData(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'data' => ['ot_token' => 'fishy-yale-expensive-jake', 'email' => 'scout@example.com'],
        ]);

        $request = UrlRedactor::scrub($event)->getRequest();

        self::assertSame(
            ['ot_token' => UrlRedactor::REDACTED, 'email' => 'scout@example.com'],
            $request['data'],
        );
    }

    public function testLeavesRequestDataWithoutOtTokenUntouched(): void
    {
        $event = Event::createEvent();
        $event->setRequest(['data' => ['email' => 'scout@example.com']]);

        self::assertSame(['email' => 'scout@example.com'], UrlRedactor::scrub($event)->getRequest()['data']);
    }

    public function testScrubsTokenBearingHeaderValuesInBothStringAndArrayShapes(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'headers' => [
                'Referer' => 'http://kissj.net/v2/event/korbo26/login?ot_token=fishy-yale-expensive-jake',
                'X-Forwarded-Uri' => ['/v2/event/cej26/tryLogin/82a97187f09d55218e59bd91188262e4'],
                'Accept' => 'text/html',
            ],
        ]);

        $headers = UrlRedactor::scrub($event)->getRequest()['headers'];

        self::assertSame(
            [
                'Referer' => 'http://kissj.net/v2/event/korbo26/login?ot_token=' . UrlRedactor::REDACTED,
                'X-Forwarded-Uri' => ['/v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED],
                'Accept' => 'text/html',
            ],
            $headers,
        );
    }

    public function testScrubsTokenFromTransactionName(): void
    {
        $event = Event::createTransaction();
        $event->setTransaction('GET /v2/event/cej26/tryLogin/82a97187f09d55218e59bd91188262e4');

        self::assertSame(
            'GET /v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED,
            UrlRedactor::scrub($event)->getTransaction(),
        );
    }

    public function testScrubsHttpContextUrl(): void
    {
        $event = Event::createEvent();
        $event->setContext('http', [
            'url' => 'http://kissj.net/v2/event/cej26/tryLogin/82a97187f09d55218e59bd91188262e4',
            'http_method' => 'GET',
        ]);

        $contexts = UrlRedactor::scrub($event)->getContexts();

        self::assertSame(
            'http://kissj.net/v2/event/cej26/tryLogin/' . UrlRedactor::REDACTED,
            $contexts['http']['url'],
        );
        self::assertSame('GET', $contexts['http']['http_method'], 'unrelated context keys must survive');
    }

    public function testScrubsMessageAndPreservesParamsAndFormatted(): void
    {
        $event = Event::createEvent();
        $event->setMessage(
            'Denied with reason: %reason%',
            ['%reason%' => 'see http://kissj.net/v2/event/korbo26/login?ot_token=fishy-yale-expensive-jake'],
            'Denied with reason: see http://kissj.net/v2/event/korbo26/login?ot_token=fishy-yale-expensive-jake',
        );

        $scrubbed = UrlRedactor::scrub($event);

        self::assertSame('Denied with reason: %reason%', $scrubbed->getMessage());
        self::assertSame(
            ['%reason%' => 'see http://kissj.net/v2/event/korbo26/login?ot_token=' . UrlRedactor::REDACTED],
            $scrubbed->getMessageParams(),
        );
        self::assertSame(
            'Denied with reason: see http://kissj.net/v2/event/korbo26/login?ot_token=' . UrlRedactor::REDACTED,
            $scrubbed->getMessageFormatted(),
        );
    }

    public function testScrubbingAnEventWithoutMessageOrRequestIsSafe(): void
    {
        $event = Event::createEvent();

        $scrubbed = UrlRedactor::scrub($event);

        self::assertNull($scrubbed->getMessage());
        self::assertSame([], $scrubbed->getRequest());
    }
}
