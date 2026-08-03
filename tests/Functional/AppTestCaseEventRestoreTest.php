<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use RuntimeException;
use Tests\AppTestCase;

class AppTestCaseEventRestoreTest extends AppTestCase
{
    public function testMutatedEventFieldsAreRestored(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        if ($container === null) {
            throw new RuntimeException('app container not available');
        }
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);
        $originalApiKey = $event->apiKeyEntry;

        $this->mutateEventForTest($container, $event, ['apiKeyEntry' => 'restore-check-' . bin2hex(random_bytes(4))]);
        self::assertNotSame($originalApiKey, $eventRepository->get($event->id)->apiKeyEntry);

        $this->restoreMutatedEventFields();

        self::assertSame($originalApiKey, $eventRepository->get($event->id)->apiKeyEntry);
    }
}
