<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Tests\AppTestCase;

class ParticipantCapacityContingentTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testSeparateContingentCountingLeavesRoomInOtherContingent(): void
    {
        $app = $this->getTestApp();
        $event = $this->seedTwoClosedContingentIsts($app, 2);

        $istThird = $this->createOpenIst($app, $event, 'capacity-third-a@example.com', 'contingent-a');

        $participantService = $this->getService($app, ParticipantService::class);
        self::assertFalse($participantService->isParticipantOrEventFull($istThird));
    }

    public function testNavigamusCountsAllContingentsTogether(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s WHERE slug = %s',
            'navigamus',
            self::TEST_EVENT_SLUG,
        );

        $event = $this->seedTwoClosedContingentIsts($app, 2);

        $istThird = $this->createOpenIst($app, $event, 'capacity-third-a-navigamus@example.com', 'contingent-a');

        $participantService = $this->getService($app, ParticipantService::class);
        self::assertTrue($participantService->isParticipantOrEventFull($istThird));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function seedTwoClosedContingentIsts(App $app, int $maximalClosedIstsCount): Event
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        $event->maximalClosedIstsCount = $maximalClosedIstsCount;
        $eventRepository->persist($event);

        $this->createClosedIst($app, $event, 'capacity-a1@example.com', 'contingent-a');
        $this->createClosedIst($app, $event, 'capacity-b1@example.com', 'contingent-b');

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createClosedIst(App $app, Event $event, string $email, string $contingent): void
    {
        $ist = $this->createOpenIst($app, $event, $email, $contingent);

        $userRepository = $this->getService($app, UserRepository::class);
        $user = $ist->getUserButNotNull();
        $user->status = UserStatus::Closed;
        $userRepository->persist($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createOpenIst(App $app, Event $event, string $email, string $contingent): Ist
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->contingent = $contingent;
        $istRepository->persist($ist);

        return $ist;
    }
}
