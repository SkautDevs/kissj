<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;

class EntryApiKeyMiddleware extends AbstractApiKeyMiddleware
{
    protected function findEventByApiKey(string $apiKey): ?Event
    {
        return $this->eventRepository->findByEntryApiKey($apiKey);
    }
}
