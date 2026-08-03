<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Log\LoggerInterface;

class VendorApiKeyMiddleware extends AbstractApiKeyMiddleware
{
    public function __construct(
        EventRepository $eventRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($eventRepository);
    }

    protected function findEventByApiKey(string $apiKey): ?Event
    {
        // a key reused across events is rejected to prevent cross-event data leakage
        $matchingEvents = $this->eventRepository->findAllByVendorApiKey($apiKey);

        if (count($matchingEvents) > 1) {
            $this->logger->error(
                sprintf(
                    'Vendor API key matched %d events - rejecting as ambiguous',
                    count($matchingEvents),
                ),
            );

            return null;
        }

        return $matchingEvents[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAdditionalAttributes(string $apiKey, Event $authorizedEvent): array
    {
        return ['allowHealthData' => $authorizedEvent->apiKeyVendorHealth === $apiKey];
    }
}
