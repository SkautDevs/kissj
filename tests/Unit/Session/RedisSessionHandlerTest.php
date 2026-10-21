<?php

declare(strict_types=1);

namespace Tests\Unit\Session;

use kissj\Session\RedisSessionHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Redis;

class RedisSessionHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testValidateIdAcceptsStoredSession(): void
    {
        $redis = Mockery::mock(Redis::class);
        $redis->shouldReceive('connect')->once()->with('redis-host', 6379);
        $redis->shouldReceive('exists')->once()->with('stored-id')->andReturn(1);

        $handler = new RedisSessionHandler($redis, 'redis-host', 6379, '');

        self::assertTrue($handler->validateId('stored-id'));
    }

    public function testValidateIdRejectsUnknownSession(): void
    {
        $redis = Mockery::mock(Redis::class);
        $redis->shouldReceive('connect')->once()->with('redis-host', 6379);
        $redis->shouldReceive('exists')->once()->with('attacker-chosen-id')->andReturn(0);

        $handler = new RedisSessionHandler($redis, 'redis-host', 6379, '');

        self::assertFalse($handler->validateId('attacker-chosen-id'));
    }

    public function testUpdateTimestampRefreshesTtlWithoutWritingData(): void
    {
        $redis = Mockery::mock(Redis::class);
        $redis->shouldReceive('connect')->once()->with('redis-host', 6379);
        $redis->shouldReceive('expire')->once()->with('stored-id', 600)->andReturn(true);
        $redis->shouldNotReceive('setex');

        $handler = new RedisSessionHandler($redis, 'redis-host', 6379, '', 600);

        self::assertTrue($handler->updateTimestamp('stored-id', 'serialized'));
    }

    public function testUpdateTimestampWritesDataWhenSessionKeyIsGone(): void
    {
        $redis = Mockery::mock(Redis::class);
        $redis->shouldReceive('connect')->once()->with('redis-host', 6379);
        $redis->shouldReceive('expire')->once()->with('stored-id', 600)->andReturn(false);
        $redis->shouldReceive('setex')->once()->with('stored-id', 600, 'serialized')->andReturn(true);

        $handler = new RedisSessionHandler($redis, 'redis-host', 6379, '', 600);

        self::assertTrue($handler->updateTimestamp('stored-id', 'serialized'));
    }
}
