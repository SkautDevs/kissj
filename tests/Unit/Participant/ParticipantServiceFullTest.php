<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
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

    public function testComingToEventCountIncludesPatrolParticipants(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        $participantService = $this->getService($app, ParticipantService::class);
        $countBefore = $participantService->getParticipantsComingToEventCount($event);

        $this->createClosedPatrol($app, $event, 'coming-count', 2);

        // leader + 2 members are 3 people, not 1
        self::assertSame($countBefore + 3, $participantService->getParticipantsComingToEventCount($event));
    }

    public function testEventCapReachedOnlyByPatrolParticipantsMarksEventFull(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        $participantService = $this->getService($app, ParticipantService::class);
        $countBefore = $participantService->getParticipantsComingToEventCount($event);

        $this->createClosedPatrol($app, $event, 'cap-block', 2);

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('cap-block-ist@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);

        $event->maximalClosedParticipantsCount = $countBefore + 3;
        $eventRepository->persist($event);
        $ist = $istRepository->get($participant->id);
        self::assertTrue($participantService->isParticipantOrEventFull($ist));

        // negative check: one head of headroom and the same gate must open again
        $event->maximalClosedParticipantsCount = $countBefore + 4;
        $eventRepository->persist($event);
        $ist = $istRepository->get($participant->id);
        self::assertFalse($participantService->isParticipantOrEventFull($ist));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createClosedPatrol(App $app, Event $event, string $emailPrefix, int $membersCount): void
    {
        $userService = $this->getService($app, UserService::class);
        $leaderUser = $userService->registerEmailUser($emailPrefix . '-leader@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');

        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        for ($i = 1; $i <= $membersCount; $i++) {
            $member = new PatrolParticipant();
            $member->patrolLeader = $patrolLeader;
            $member->email = $emailPrefix . '-member-' . $i . '@example.com';
            $participantRepository->persist($member);
        }

        $leaderUser->status = UserStatus::Closed;
        $this->getService($app, UserRepository::class)->persist($leaderUser);
    }
}
