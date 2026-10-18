<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class AdminMendLeaderCancelNoteTest extends AppTestCase
{
    private const string NOTE = 'Zrušení vedoucího zatím není podporované';
    private const string BASE_URL = '/v2/event/test-slug';

    public function testMendPageOfPatrolLeaderShowsNoteInsteadOfCancelForm(): void
    {
        $container = $this->getTestApp()->getContainer();
        $_SESSION['user'] = ['id' => $this->createEventAdmin($container)->id];
        $patrolLeader = $this->createPaidParticipant($container, 'pl');

        $body = $this->fetchMendPage($patrolLeader);

        self::assertStringContainsString(self::NOTE, $body);
        self::assertStringNotContainsString('/changeRole/' . $patrolLeader->id . '/cancel', $body);
    }

    public function testMendPageOfTechnicalTeamLeaderShowsNoteInsteadOfCancelForm(): void
    {
        $container = $this->getTestApp()->getContainer();
        $_SESSION['user'] = ['id' => $this->createEventAdmin($container)->id];
        $technicalTeamLeader = $this->createPaidParticipant($container, 'ttl');

        $body = $this->fetchMendPage($technicalTeamLeader);

        self::assertStringContainsString(self::NOTE, $body);
        self::assertStringNotContainsString('/changeRole/' . $technicalTeamLeader->id . '/cancel', $body);
    }

    public function testMendPageOfIstShowsCancelFormWithoutNote(): void
    {
        $container = $this->getTestApp()->getContainer();
        $_SESSION['user'] = ['id' => $this->createEventAdmin($container)->id];
        $ist = $this->createPaidParticipant($container, 'ist');

        $body = $this->fetchMendPage($ist);

        self::assertStringContainsString('/changeRole/' . $ist->id . '/cancel', $body);
        self::assertStringNotContainsString(self::NOTE, $body);
    }

    private function fetchMendPage(Participant $participant): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/' . $participant->id . '/mend',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    private function createPaidParticipant(ContainerInterface $container, string $role): Participant
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = $userService->registerEmailUser(
            $role . '-mend-' . bin2hex(random_bytes(4)) . '@example.com',
            $this->getTestSlugEvent($eventRepository),
        );
        $participant = $userService->createParticipantSetRole($user, $role);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
    }

    private function createEventAdmin(ContainerInterface $container): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = new User();
        $user->event = $this->getTestSlugEvent($eventRepository);
        $user->role = UserRole::Admin;
        $user->email = 'admin-mend-' . bin2hex(random_bytes(4)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }
}
