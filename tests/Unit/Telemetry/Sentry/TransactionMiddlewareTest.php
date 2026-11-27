<?php

declare(strict_types=1);

namespace Tests\Unit\Telemetry\Sentry;

use kissj\Telemetry\Sentry\TransactionMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class TransactionMiddlewareTest extends TestCase
{
    private HubInterface $previousHub;

    protected function setUp(): void
    {
        $this->previousHub = SentrySdk::getCurrentHub();
    }

    protected function tearDown(): void
    {
        SentrySdk::setCurrentHub($this->previousHub);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function pathProvider(): array
    {
        return [
            'image' => [
                '/v3/programme/image/0123456789abcdef0123456789abcdef',
                'GET /v3/programme/image/{file}',
            ],
            'thumb' => [
                '/v3/programme/image/0123456789abcdef0123456789abcdef/thumb',
                'GET /v3/programme/image/{file}/thumb',
            ],
            'numeric id' => [
                '/v2/event/slug/admin/programmes/42/edit',
                'GET /v2/event/slug/admin/programmes/{id}/edit',
            ],
            'too short hex' => [
                '/v3/programme/image/0123456789abcdef0123456789abcde',
                'GET /v3/programme/image/0123456789abcdef0123456789abcde',
            ],
            'too long hex' => [
                '/v3/programme/image/0123456789abcdef0123456789abcdef0',
                'GET /v3/programme/image/0123456789abcdef0123456789abcdef0',
            ],
            'uppercase hex' => [
                '/v3/programme/image/0123456789ABCDEF0123456789ABCDEF',
                'GET /v3/programme/image/0123456789ABCDEF0123456789ABCDEF',
            ],
            'non hex letters' => [
                '/v3/programme/image/0123456789abcdef0123456789abcdez',
                'GET /v3/programme/image/0123456789abcdef0123456789abcdez',
            ],
            'all digits win' => [
                '/v3/programme/image/01234567890123456789012345678901',
                'GET /v3/programme/image/{id}',
            ],
        ];
    }

    #[DataProvider('pathProvider')]
    public function testTransactionNameGroupsVariableSegments(string $path, string $expectedName): void
    {
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
        $client = ClientBuilder::create([
            'dsn' => 'https://public@example.com/1',
            'traces_sample_rate' => 1.0,
            'default_integrations' => false,
        ])->setTransport($transport)->getClient();
        SentrySdk::setCurrentHub(new Hub($client));

        (new TransactionMiddleware())->process(
            (new ServerRequestFactory())->createServerRequest('GET', 'http://kissj.net' . $path),
            new class () implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return (new ResponseFactory())->createResponse(200);
                }
            },
        );

        $sentEvent = $transport->sentEvent;
        self::assertInstanceOf(Event::class, $sentEvent);
        self::assertSame($expectedName, $sentEvent->getTransaction());
    }
}
