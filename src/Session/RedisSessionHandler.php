<?php

declare(strict_types=1);

namespace kissj\Session;

use Redis;
use SessionHandlerInterface;

readonly class RedisSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        private Redis $redis,
        string $address,
        int $port,
        string $host,
        string $password,
        private int $ttl = 60 * 60 * 24 * 7, // one week in seconds
    ) {
        $this->redis->connect($address, $port);
        $this->redis->auth([$host => $password]);
    }

    public function open(string $path, string $name): bool
    {
        // no action needed
        return true;
    }

    public function close(): bool
    {
        return $this->redis->close();
    }

    public function read(string $id): string|false
    {
        return $this->redis->get($id) ?: '';
    }

    public function write(string $id, string $data): bool
    {
        return $this->redis->setex($id, $this->ttl, $data);
    }

    public function destroy(string $id): bool
    {
        $this->redis->del($id);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        // no action needed
        return 0;
    }
}
