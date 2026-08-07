<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class AdminUnleaveParticipantTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testUnleaveClearsLeaveDate(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $participant = $this->createIstWithLeaveDate($container, UserStatus::Paid);

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/' . $participant->id . '/unleave',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/mend', $response->getHeaderLine('Location'));

        $freshRepository = $this->getService($this->getTestApp(false), ParticipantRepository::class);
        /** @var Participant $freshParticipant */
        $freshParticipant = $freshRepository->get($participant->id);
        self::assertNull($freshParticipant->leaveDate);
        // reverting a leave must not disturb the entry - the four setAs* siblings invite a copy-paste slip
        self::assertNotNull($freshParticipant->entryDate);
    }

    public function testMendPageShowsUnleaveFormForLeavedParticipant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $participant = $this->createIstWithLeaveDate($container, UserStatus::Paid);

        $body = $this->fetchMendPage($participant);

        self::assertStringContainsString('/admin/' . $participant->id . '/unleave', $body);
        // positive control for the not-paid test below - showDetails renders only inside the paid-only block
        self::assertStringContainsString('/admin/' . $participant->id . '/showDetails', $body);
    }

    public function testUnleaveFormShowsForNotPaidParticipant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $participant = $this->createIstWithLeaveDate($container, UserStatus::Open);

        $body = $this->fetchMendPage($participant);

        // pins the deliberate decision - the unleave button must never be nested in the paid-only block
        self::assertStringContainsString('/admin/' . $participant->id . '/unleave', $body);
        // showDetails renders unconditionally inside that block, proving it really was skipped here
        self::assertStringNotContainsString('/admin/' . $participant->id . '/showDetails', $body);
    }

    private function fetchMendPage(Participant $participant): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/' . $participant->id . '/mend',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    private function createIstWithLeaveDate(ContainerInterface $container, UserStatus $status): Participant
    {
        $participant = $this->createOpenIst($container, 'Leaved', 'Ist' . bin2hex(random_bytes(4)));

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $userRepository->persist($user);

        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);
        $participant->entryDate = DateTimeUtils::getDateTime();
        $participant->leaveDate = DateTimeUtils::getDateTime();
        $participantRepository->persist($participant);

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
        $user->email = 'admin-unleave-' . bin2hex(random_bytes(4)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }
}
