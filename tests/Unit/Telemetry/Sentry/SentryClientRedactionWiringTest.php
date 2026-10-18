<?php

declare(strict_types=1);

namespace Tests\Unit\Telemetry\Sentry;

use kissj\Settings\Settings;
use PHPUnit\Framework\TestCase;
use Sentry\Breadcrumb;
use Sentry\Client as SentryClient;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\ExceptionDataBag;
use Sentry\Options;
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

        return $this->encodeRedactableFields($sentEvent);
    }

    private function encodeRedactableFields(Event $event): string
    {
        $user = $event->getUser();

        return json_encode([
            'transaction' => $event->getTransaction(),
            'message' => $event->getMessage(),
            'message_formatted' => $event->getMessageFormatted(),
            'message_params' => $event->getMessageParams(),
            'request' => $event->getRequest(),
            'contexts' => $event->getContexts(),
            'extra' => $event->getExtra(),
            'tags' => $event->getTags(),
            'user' => $user === null ? null : [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'ip_address' => $user->getIpAddress(),
                'metadata' => $user->getMetadata(),
            ],
            'breadcrumbs' => array_map(
                static fn (Breadcrumb $breadcrumb): array => [
                    'category' => $breadcrumb->getCategory(),
                    'message' => $breadcrumb->getMessage(),
                    'metadata' => $breadcrumb->getMetadata(),
                ],
                $event->getBreadcrumbs(),
            ),
            'exceptions' => array_map(
                static fn (ExceptionDataBag $exception): string => $exception->getValue(),
                $event->getExceptions(),
            ),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
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
