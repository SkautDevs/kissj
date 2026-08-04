<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class AdminListingPagesTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testPaidPageListsPaidParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $this->createPaidIst($container, 'Paid', 'PaidIst' . $suffix);
        $this->createOpenIst($container, 'Open', 'OpenIst' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/showPaid');

        self::assertStringContainsString('PaidIst' . $suffix, $body);
        self::assertStringNotContainsString('OpenIst' . $suffix, $body);
    }

    public function testOpenPageListsOpenParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $this->createOpenIst($container, 'Open', 'OpenIst' . $suffix);
        $this->createPaidIst($container, 'Paid', 'PaidIst' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/showOpen');

        self::assertStringContainsString('OpenIst' . $suffix, $body);
        self::assertStringNotContainsString('PaidIst' . $suffix, $body);
    }

    public function testApprovingPageListsClosedParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $this->createIstWithStatus($container, 'Closed', 'ClosedIst' . $suffix, UserStatus::Closed);
        $this->createPaidIst($container, 'Paid', 'PaidIst' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/approving');

        self::assertStringContainsString('ClosedIst' . $suffix, $body);
        self::assertStringNotContainsString('PaidIst' . $suffix, $body);
    }

    public function testApprovingPageRendersPatrolLeaderWithChildren(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($container, $event, ['allowPatrols' => true]);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $patrolLeader = $this->createClosedPatrolLeader($container, 'Leader' . $suffix, 'Parent' . $suffix);
        $this->addPatrolParticipantChild($container, $patrolLeader, 'Child' . $suffix, 'Member' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/approving');

        // the parent leader's name is rendered ...
        self::assertStringContainsString('Leader' . $suffix, $body);
        // ... and so is the child, proving the childContentArbiter include did not blow up
        self::assertStringContainsString('Child' . $suffix, $body);
    }

    public function testPaymentsPageListsApprovedParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $this->createIstWithStatus($container, 'Approved', 'ApprovedIst' . $suffix, UserStatus::Approved);
        $this->createPaidIst($container, 'Paid', 'PaidIst' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/payments');

        self::assertStringContainsString('ApprovedIst' . $suffix, $body);
        self::assertStringNotContainsString('PaidIst' . $suffix, $body);
    }

    public function testShowPaidRendersExactlyFiveEnabledRoleHeadingsAndExcludesTroopParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($container, $event, [
            'allowPatrols' => true,
            'allowIsts' => true,
            'allowGuests' => true,
            'allowTroops' => true,
            'allowOrganizingTeam' => true,
        ]);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        // showPaid never carries a 'tp' section (only ist/pl/tl/guest/ot) - a paid troop
        // participant existing proves that absence is deliberate, not just "nothing to show"
        $suffix = bin2hex(random_bytes(4));
        $this->createPaidTroopParticipant($container, 'Hidden', 'TroopParticipant' . $suffix);

        $body = $this->fetchBody(self::BASE_URL . '/admin/showPaid');

        // stats-admin.twig renders exactly one unconditional <h4> per section (even when
        // empty), so counting them pins the role list without depending on translated text -
        // the default test locale is cs, where role.tp and role.p happen to share one word
        // ("účastník"), which would make a text-based assertion unreliable
        self::assertSame(5, substr_count($body, '<h4>'));
        self::assertStringNotContainsString('TroopParticipant' . $suffix, $body);
    }

    public function testPaymentsSectionsPinnedToFourRolesAndShowsEmptyStateMessage(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($container, $event, [
            'allowPatrols' => true,
            'allowIsts' => true,
            'allowTroops' => true,
            'allowOrganizingTeam' => true,
        ]);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $body = $this->fetchBody(self::BASE_URL . '/admin/payments');

        // payments-admin.twig renders one static page-header <h2> plus exactly one <h2> per
        // section (title or empty-state) - counting pins the role list (ist/pl/tl/ot, no tp,
        // no guest) without depending on translated text
        self::assertSame(5, substr_count($body, '<h2>'));
        // no approved troop leader exists for this event, so the empty-state message must show
        self::assertStringContainsString('Všichni vedoucí mají zaplaceno!', $body);
    }

    public function testDisabledRoleRendersNoSection(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $suffix = bin2hex(random_bytes(4));
        $this->createOpenIst($container, 'Hidden', 'HiddenIst' . $suffix);

        $this->mutateEventForTest($container, $event, ['allowIsts' => false]);

        $body = $this->fetchBody(self::BASE_URL . '/admin/showOpen');

        self::assertStringNotContainsString('HiddenIst' . $suffix, $body);
    }

    public function testDisabledRoleRendersNoSectionAcrossAdminPages(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $this->mutateEventForTest($container, $event, ['allowIsts' => false]);

        // probe lives in lastName alone - getFullName() inserts a space between fields
        $suffix = bin2hex(random_bytes(4));
        $this->createPaidIst($container, 'Hidden', 'HiddenPaidIst' . $suffix);
        $this->createIstWithStatus($container, 'Hidden', 'HiddenClosedIst' . $suffix, UserStatus::Closed);
        $this->createIstWithStatus($container, 'Hidden', 'HiddenApprovedIst' . $suffix, UserStatus::Approved);

        $hiddenNamesByPath = [
            '/admin/showPaid' => 'HiddenPaidIst' . $suffix,
            '/admin/approving' => 'HiddenClosedIst' . $suffix,
            '/admin/payments' => 'HiddenApprovedIst' . $suffix,
        ];

        foreach ($hiddenNamesByPath as $path => $hiddenName) {
            $body = $this->fetchBody(self::BASE_URL . $path);
            self::assertStringNotContainsString($hiddenName, $body);
        }
    }

    private function fetchBody(string $path): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest($path));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    private function createIstWithStatus(
        ContainerInterface $container,
        string $firstName,
        string $lastName,
        UserStatus $status,
    ): Participant {
        $participant = $this->createOpenIst($container, $firstName, $lastName);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $userRepository->persist($user);

        return $participant;
    }

    private function createPaidTroopParticipant(
        ContainerInterface $container,
        string $firstName,
        string $lastName,
    ): Participant {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);

        $event = $this->getTestSlugEvent($eventRepository);
        $email = 'admin-listing-tp-' . bin2hex(random_bytes(6)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'tp');
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
    }

    private function createClosedPatrolLeader(
        ContainerInterface $container,
        string $firstName,
        string $lastName,
    ): PatrolLeader {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);
        /** @var PatrolLeaderRepository $patrolLeaderRepository */
        $patrolLeaderRepository = $container->get(PatrolLeaderRepository::class);

        $event = $this->getTestSlugEvent($eventRepository);
        $email = 'admin-listing-pl-' . bin2hex(random_bytes(6)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'pl');
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participantRepository->persist($participant);
        $user->status = UserStatus::Closed;
        $userRepository->persist($user);

        return $patrolLeaderRepository->get($participant->id);
    }

    private function addPatrolParticipantChild(
        ContainerInterface $container,
        PatrolLeader $patrolLeader,
        string $firstName,
        string $lastName,
    ): void {
        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);

        $participant = new PatrolParticipant();
        $participant->patrolLeader = $patrolLeader;
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $patrolParticipantRepository->persist($participant);
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
        $user->email = 'admin-listing-' . bin2hex(random_bytes(6)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }
}
