<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;

class EventApiKeyMiddleware extends AbstractApiKeyMiddleware
{
    protected function findEventByApiKey(string $apiKey): ?Event
    {
        return $this->eventRepository->findByEventApiKey($apiKey);
    }
}
