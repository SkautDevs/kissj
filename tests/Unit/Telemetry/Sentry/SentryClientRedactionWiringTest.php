<?php

declare(strict_types=1);

namespace Tests\Unit\Telemetry\Sentry;

use kissj\Settings\Settings;
use PHPUnit\Framework\TestCase;
use Sentry\Client as SentryClient;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Options;
use Sentry\Serializer\PayloadSerializer;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

// constrains the Settings.php wiring, not the redactor: a callback registered for one event type fails here
class SentryClientRedactionWiringTest extends TestCase
{
    private const string LOGIN_TOKEN = '82a97187f09d55218e59bd91188262e4';
    private const string OT_TOKEN = 'fishy-yale-expensive-jake';

    public function testErrorEventsLeaveTheClientWithoutRawTokens(): void
    {
        $event = $this->fillWithTokens(Event::createEvent());

        self::assertStringsAbsent($this->capturePayload($event));
    }

    public function testTransactionEventsLeaveTheClientWithoutRawTokens(): void
    {
        $transaction = Event::createTransaction();
        $transaction->setTransaction('GET /v2/event/cej26/tryLogin/' . self::LOGIN_TOKEN);
        $transaction->setStartTimestamp(microtime(true) - 1.0);

        self::assertStringsAbsent($this->capturePayload($this->fillWithTokens($transaction)));
    }

    private function fillWithTokens(Event $event): Event
    {
        $event->setRequest([
            'url' => 'http://kissj.net/v2/event/cej26/tryLogin/' . self::LOGIN_TOKEN,
            'query_string' => 'ot_token=' . self::OT_TOKEN,
            'method' => 'POST',
            'data' => ['ot_token' => self::OT_TOKEN, 'email' => 'scout@example.com'],
            'headers' => ['Referer' => 'http://kissj.net/v2/event/korbo26/login?ot_token=' . self::OT_TOKEN],
        ]);
        $event->setContext('http', [
            'url' => 'http://kissj.net/v2/event/cej26/tryLogin/' . self::LOGIN_TOKEN,
            'http_method' => 'POST',
        ]);

        return $event;
    }

    private function capturePayload(Event $event): string
    {
        $options = $this->getProductionSentryOptions();
        $transport = new class () implements TransportInterface {
            public ?Event $sentEvent = null;

            public function send(Event $event): Result
            {
                $this->sentEvent = $event;

                return new Result(ResultStatus::success(), $event);
            }

            public function close(?int $timeout = null): Result
            {
                return new Result(ResultStatus::success());
            }
        };

        $client = (new ClientBuilder($options))->setTransport($transport)->getClient();
        $client->captureEvent($event);

        $sentEvent = $transport->sentEvent;
        self::assertInstanceOf(Event::class, $sentEvent, 'the event never reached the transport');

        return (new PayloadSerializer($options))->serialize($sentEvent);
    }

    private function getProductionSentryOptions(): Options
    {
        $definition = (new Settings())->getContainerDefinition(
            __DIR__ . '/../../../',
            'env.testing',
            __DIR__ . '/../../../temp',
        );

        $client = $definition[SentryClient::class] ?? null;
        self::assertInstanceOf(SentryClient::class, $client);

        return $client->getOptions();
    }

    private static function assertStringsAbsent(string $payload): void
    {
        self::assertStringNotContainsString(self::LOGIN_TOKEN, $payload);
        self::assertStringNotContainsString(self::OT_TOKEN, $payload);
    }
}
