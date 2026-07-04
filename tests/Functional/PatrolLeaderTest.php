<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class PatrolLeaderTest extends AppTestCase
{
    public function testCreatePatrolLeader(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $testEvent = $eventRepository->get(1);

        $patrolService = $this->getService($app, PatrolService::class);

        $email = 'test@example.com';
        $user = $userService->registerEmailUser($email, $testEvent);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        self::assertEquals($user->id, $patrolLeader->getUserButNotNull()->id);
        self::assertEquals(ParticipantRole::PatrolLeader, $patrolLeader->role);
        self::assertEquals(UserRole::Participant, $patrolLeader->getUserButNotNull()->role);
        self::assertEquals(UserStatus::WithoutRole, $patrolLeader->getUserButNotNull()->status);
    }
}
