<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use kissj\Application\HealthController;
use kissj\Event\EventRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Slim\Psr7\Response;

class HealthControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCheckReturnsErrorWhenDatabasePingFails(): void
    {
        $exception = new RuntimeException('secret db detail');
        $eventRepository = Mockery::mock(EventRepository::class);
        $eventRepository->shouldReceive('countBy')->with([])->andThrow($exception);

        $logger = Mockery::mock(Logger::class);
        $logger->shouldReceive('error')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::on(
                    /**
                     * @param array<string, mixed> $context
                     */
                    static function (array $context) use ($exception): bool {
                        return $context['exception'] === $exception;
                    },
                ),
            );

        $controller = new HealthController($eventRepository);
        $loggerProperty = new ReflectionProperty($controller, 'logger');
        $loggerProperty->setValue($controller, $logger);

        $response = $controller->check(new Response());

        self::assertSame(500, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertJsonStringEqualsJsonString('{"status":"error"}', $body);
        self::assertStringNotContainsString('secret db detail', $body);
    }
}
