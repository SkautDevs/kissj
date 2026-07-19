<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserService;
use RuntimeException;
use Tests\AppTestCase;

class ParticipantServiceFullTest extends AppTestCase
{
    public function testRoleFullReturnsTrue(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        // no closed ISTs yet, so a zero cap already means the role is full
        $event->maximalClosedIstsCount = 0;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('role-full-test@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $participantService = $this->getService($app, ParticipantService::class);
        self::assertTrue($participantService->isParticipantOrEventFull($ist));
    }

    public function testEventCapReachedReturnsTrue(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        // role cap left with room, but event-wide cap is already reached
        $event->maximalClosedParticipantsCount = 0;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('event-full-test@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $participantService = $this->getService($app, ParticipantService::class);
        self::assertTrue($participantService->isParticipantOrEventFull($ist));
    }

    public function testNeitherFullReturnsFalse(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('not-full-test@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $participantService = $this->getService($app, ParticipantService::class);
        self::assertFalse($participantService->isParticipantOrEventFull($ist));
    }
}
