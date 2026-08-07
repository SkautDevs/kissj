<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class EventTypePatrolPriceTest extends AppTestCase
{
    public function testNavigamusPatrolLeaderPriceCoversWholePatrol(): void
    {
        $app = $this->getTestApp();
        $patrolLeader = $this->makePatrolWithTwoParticipants($app);

        self::assertSame((2 + 1) * 2800, (new EventTypeNavigamus())->getPrice($patrolLeader));
    }

    public function testAquaPatrolLeaderPriceWithoutSelfEatingDiscount(): void
    {
        $app = $this->getTestApp();
        $patrolLeader = $this->makePatrolWithTwoParticipants($app);

        self::assertSame((2 + 1) * 160, (new EventTypeAqua())->getPrice($patrolLeader));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makePatrolWithTwoParticipants(App $app): PatrolLeader
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $event = $this->getSmallTestEvent($eventRepository);

        $leaderEmail = 'price-pl-' . bin2hex(random_bytes(4)) . '@example.com';
        $leaderUser = $userService->registerEmailUser($leaderEmail, $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Priced';
        $patrolLeaderRepository->persist($patrolLeader);

        $firstMember = new PatrolParticipant();
        $firstMember->patrolLeader = $patrolLeader;
        $firstMember->firstName = 'Member';
        $firstMember->lastName = 'One';
        $patrolParticipantRepository->persist($firstMember);

        $secondMember = new PatrolParticipant();
        $secondMember->patrolLeader = $patrolLeader;
        $secondMember->firstName = 'Member';
        $secondMember->lastName = 'Two';
        $patrolParticipantRepository->persist($secondMember);

        return $patrolLeader;
    }
}
