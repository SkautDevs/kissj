<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\ApplicationGetter;
use kissj\Application\DateTimeUtils;
use kissj\Telemetry\Sentry\UrlRedactor;
use LogicException;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Sentry\Client as SentryClient;
use Sentry\Logs\Log;
use Sentry\Logs\LogLevel;
use Sentry\Monolog\ExceptionToSentryIssueHandler;
use Sentry\Monolog\LogsHandler;
use Sentry\Monolog\LogToSentryIssueHandler;
use Tests\AppTestCase;

class LoggerHandlersTest extends AppTestCase
{
    public function testSentryExceptionHandlerStartsAtWarning(): void
    {
        $handler = $this->findHandler(ExceptionToSentryIssueHandler::class);

        self::assertSame(Level::Warning, $handler->getLevel());
    }

    public function testSentryLogIssueHandlerStartsAtWarning(): void
    {
        $handler = $this->findHandler(LogToSentryIssueHandler::class);

        self::assertSame(Level::Warning, $handler->getLevel());
    }

    public function testSentryLogsHandlerTakesInfo(): void
    {
        $handler = $this->findHandler(LogsHandler::class);

        self::assertTrue($handler->isHandling($this->createRecord(Level::Info)));
        self::assertFalse($handler->isHandling($this->createRecord(Level::Debug)));
    }

    public function testSentryClientFlushesLogsInBatchesOfHundred(): void
    {
        self::assertSame(100, $this->getSentryClient()->getOptions()->getLogFlushThreshold());
    }

    public function testSentryClientRedactsLoginTokensInLogs(): void
    {
        $log = new Log(1.0, 'trace', LogLevel::info(), 'Sent email');
        $log->setAttribute('url', '/v2/kissj/tryLogin/abc123');

        $scrubbed = ($this->getSentryClient()->getOptions()->getBeforeSendLogCallback())($log);

        self::assertInstanceOf(Log::class, $scrubbed);
        self::assertSame(
            '/v2/kissj/tryLogin/' . UrlRedactor::REDACTED,
            $scrubbed->attributes()->get('url')?->getValue(),
        );
    }

    private function getSentryClient(): SentryClient
    {
        $client = $this->getContainer()->get(SentryClient::class);
        self::assertInstanceOf(SentryClient::class, $client);

        return $client;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function findHandler(string $class): object
    {
        $logger = $this->getContainer()->get(Logger::class);
        self::assertInstanceOf(Logger::class, $logger);

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof $class) {
                return $handler;
            }
        }

        throw new LogicException('Handler ' . $class . ' is not registered on the logger');
    }

    private function getContainer(): ContainerInterface
    {
        return (new ApplicationGetter())->getApp(
            __DIR__ . '/../',
            'env.testing',
            $this->getRunTempPath(),
        )->getContainer();
    }

    private function createRecord(Level $level): LogRecord
    {
        return new LogRecord(DateTimeUtils::getDateTime(), 'test', $level, 'message');
    }
}
