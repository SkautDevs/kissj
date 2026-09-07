<?php

declare(strict_types=1);

namespace Tests\Unit\Logging\Monolog;

use kissj\Application\DateTimeUtils;
use kissj\Logging\Monolog\EventContextProcessor;
use kissj\Logging\Monolog\UserContextProcessor;
use kissj\User\User;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserContextProcessorTest extends TestCase
{
    public function testKeepsCallerContextAndAddsAnonymousUser(): void
    {
        $record = new LogRecord(
            DateTimeUtils::getDateTime(),
            'KISSJ',
            Level::Warning,
            'Failed to read Skautis login data',
            ['reason' => 'bad date'],
        );

        $processor = new UserContextProcessor(null);
        $result = $processor($record);

        self::assertSame('bad date', $result->context['reason']);
        self::assertSame([
            'authenticated' => false,
            'id' => null,
            'email' => null,
        ], $result->context['user']);
    }

    public function testAddsAuthenticatedUser(): void
    {
        $user = new User();
        $user->id = 3;
        $user->email = 'a@a.a';

        $record = new LogRecord(
            DateTimeUtils::getDateTime(),
            'KISSJ',
            Level::Warning,
            'User action',
            ['throwable' => new RuntimeException('x')],
        );

        $processor = new UserContextProcessor($user);
        $result = $processor($record);

        self::assertInstanceOf(RuntimeException::class, $result->context['throwable']);
        self::assertSame(['authenticated' => true, 'id' => 3, 'email' => 'a@a.a'], $result->context['user']);
    }

    public function testMiddlewareProcessorOrderKeepsAllContext(): void
    {
        $handler = new TestHandler();
        $logger = new Logger('KISSJ', [$handler]);
        // same push order as MonologContextMiddleware
        $logger->pushProcessor(new UserContextProcessor(null));
        $logger->pushProcessor(new EventContextProcessor(null));

        $logger->warning('Chain test', ['reason' => 'test reason']);

        self::assertCount(1, $handler->getRecords());
        $context = $handler->getRecords()[0]->context;
        self::assertSame('test reason', $context['reason']);
        self::assertSame(['id' => null, 'slug' => null, 'readableName' => null], $context['event']);
        self::assertSame(['authenticated' => false, 'id' => null, 'email' => null], $context['user']);
    }
}
