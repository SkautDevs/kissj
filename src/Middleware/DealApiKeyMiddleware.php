<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;

class DealApiKeyMiddleware extends AbstractApiKeyMiddleware
{
    protected function findEventByApiKey(string $apiKey): ?Event
    {
        return $this->eventRepository->findByDealApiKey($apiKey);
    }
}
