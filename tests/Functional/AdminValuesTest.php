<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\Admin\AdminValuesSaveResult;
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

class AdminValuesTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testAdminValuesColumnsPersist(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(4);
        self::assertSame(self::TEST_EVENT_SLUG, $event->slug);

        $participant = $this->createPaidIst($container, 'Round', 'Trip' . bin2hex(random_bytes(4)));
        $participant->subcamp = 'detail.subcamp.theba';
        $participant->internalUniqueId = 'CEJ-RT-1';
        $participant->internalCommonId = 'G-RT';
        $participantRepository->persist($participant);

        $reloaded = $participantRepository->getParticipantById($participant->id, $event);
        self::assertSame('detail.subcamp.theba', $reloaded->subcamp);
        self::assertSame('CEJ-RT-1', $reloaded->internalUniqueId);
        self::assertSame('G-RT', $reloaded->internalCommonId);
    }

    public function testSetAdminValuesValidatesAndPersists(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $adminService = $this->getService($app, AdminService::class);
            $participantRepository = $this->getService($app, ParticipantRepository::class);
            $eventRepository = $this->getService($app, EventRepository::class);
            $event = $eventRepository->get(4);

            $suffix = bin2hex(random_bytes(4));
            $participant = $this->createPaidIst($container, 'Jana', 'Novakova' . $suffix);
            $other = $this->createPaidIst($container, 'Petr', 'Svoboda' . $suffix);

            $result = $adminService->setAdminValues($participant, $event, EventTypeCej::SUBCAMP_THEBA, 'CEJ-001-' . $suffix, 'G-7');
            self::assertSame(AdminValuesSaveResult::Saved, $result);

            $reloaded = $participantRepository->getParticipantById($participant->id, $event);
            self::assertSame(EventTypeCej::SUBCAMP_THEBA, $reloaded->subcamp);
            self::assertSame('CEJ-001-' . $suffix, $reloaded->internalUniqueId);
            self::assertSame('G-7', $reloaded->internalCommonId);

            self::assertSame(
                AdminValuesSaveResult::UniqueIdTaken,
                $adminService->setAdminValues($other, $event, null, 'CEJ-001-' . $suffix, null),
            );
            self::assertSame(
                AdminValuesSaveResult::UnknownSubcamp,
                $adminService->setAdminValues($other, $event, 'detail.subcamp.nonexistent', null, null),
            );

            // rejected saves must not have touched the participant
            $otherReloaded = $participantRepository->getParticipantById($other->id, $event);
            self::assertNull($otherReloaded->subcamp);
            self::assertNull($otherReloaded->internalUniqueId);

            // re-saving the same participant with its own unique ID is not a conflict
            self::assertSame(
                AdminValuesSaveResult::Saved,
                $adminService->setAdminValues($participant, $event, EventTypeCej::SUBCAMP_SPARTA, 'CEJ-001-' . $suffix, null),
            );
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testSetAdminValuesTreatsEmptyStringsAsNull(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $adminService = $this->getService($app, AdminService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(4);

        $suffix = bin2hex(random_bytes(4));
        $first = $this->createPaidIst($container, 'Prazdny', 'Prvni' . $suffix);
        $second = $this->createPaidIst($container, 'Prazdny', 'Druhy' . $suffix);

        // HTML forms post empty strings for blank inputs
        self::assertSame(
            AdminValuesSaveResult::Saved,
            $adminService->setAdminValues($first, $event, '', '', ''),
        );

        $reloaded = $participantRepository->getParticipantById($first->id, $event);
        self::assertNull($reloaded->subcamp);
        self::assertNull($reloaded->internalUniqueId);
        self::assertNull($reloaded->internalCommonId);

        // blank unique IDs must never collide with each other
        self::assertSame(
            AdminValuesSaveResult::Saved,
            $adminService->setAdminValues($second, $event, '', '', ''),
        );
    }

    public function testUniqueIdHeldByOpenParticipantIsRefused(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $adminService = $this->getService($app, AdminService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(4);

        $suffix = bin2hex(random_bytes(4));
        $openHolder = $this->createOpenIst($container, 'Otevreny', 'Drzitel' . $suffix);
        $openHolder->internalUniqueId = 'OPEN-' . $suffix;
        $participantRepository->persist($openHolder);

        // the open holder stays out of the conservative matching pool, but still owns the ID
        $poolIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $participantRepository->getEventParticipantsForAdminValues($event),
        );
        self::assertNotContains($openHolder->id, $poolIds);

        $applicant = $this->createPaidIst($container, 'Zadatel', 'Placeny' . $suffix);
        self::assertSame(
            AdminValuesSaveResult::UniqueIdTaken,
            $adminService->setAdminValues($applicant, $event, null, 'OPEN-' . $suffix, null),
        );

        $applicantReloaded = $participantRepository->getParticipantById($applicant->id, $event);
        self::assertNull($applicantReloaded->internalUniqueId);

        // the open holder may still re-save its own ID
        self::assertSame(
            AdminValuesSaveResult::Saved,
            $adminService->setAdminValues($openHolder, $event, null, 'OPEN-' . $suffix, 'G-O'),
        );
    }

    public function testAdminValuesPoolContainsLoginlessPatrolParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(4);

        $suffix = bin2hex(random_bytes(4));
        $patrolLeader = $this->createPaidPatrolLeader($container, 'Vera', 'Vedouci' . $suffix);

        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->firstName = 'Pavel';
        $patrolParticipant->lastName = 'Clen' . $suffix;
        $patrolParticipantRepository->persist($patrolParticipant);

        $ids = array_map(
            static fn (Participant $participant): int => $participant->id,
            $participantRepository->getEventParticipantsForAdminValues($event),
        );

        self::assertContains($patrolLeader->id, $ids);
        self::assertContains($patrolParticipant->id, $ids);
        self::assertSame(array_values(array_unique($ids)), $ids);
    }

    public function testChangeAdminValuesRouteSetsValues(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $participantRepository = $this->getService($app, ParticipantRepository::class);
            $eventRepository = $this->getService($app, EventRepository::class);
            $event = $eventRepository->get(4);

            $adminUser = $this->createEventAdmin($container);
            $_SESSION['user'] = ['id' => $adminUser->id];

            $suffix = bin2hex(random_bytes(4));
            $participant = $this->createPaidIst($container, 'Karel', 'Route' . $suffix);

            $response = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/admin/' . $participant->id . '/adminValues',
                'POST',
                [
                    'subcamp' => EventTypeCej::SUBCAMP_ATHENS,
                    'internalUniqueId' => 'CEJ-R-' . $suffix,
                    'internalCommonId' => 'G-R',
                ],
            ));
            self::assertSame(302, $response->getStatusCode());

            $reloaded = $participantRepository->getParticipantById($participant->id, $event);
            self::assertSame(EventTypeCej::SUBCAMP_ATHENS, $reloaded->subcamp);
            self::assertSame('CEJ-R-' . $suffix, $reloaded->internalUniqueId);
            self::assertSame('G-R', $reloaded->internalCommonId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testChangeAdminValuesRouteRedirectsAwayWhenEventHasNoSubcamps(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        // event 4 stays 'default' - no subcamps
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(4);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $participant = $this->createPaidIst($container, 'Bez', 'Podtabora' . bin2hex(random_bytes(4)));

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/' . $participant->id . '/adminValues',
            'POST',
            ['subcamp' => '', 'internalUniqueId' => 'X-1', 'internalCommonId' => ''],
        ));
        self::assertSame(302, $response->getStatusCode());

        $reloaded = $participantRepository->getParticipantById($participant->id, $event);
        self::assertNull($reloaded->internalUniqueId);
    }

    public function testMendPageShowsAdminValuesFormOnlyForEventWithSubcamps(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $adminUser = $this->createEventAdmin($container);
        $_SESSION['user'] = ['id' => $adminUser->id];

        $participant = $this->createPaidIst($container, 'Formular', 'Mend' . bin2hex(random_bytes(4)));
        $mendUrl = self::BASE_URL . '/admin/' . $participant->id . '/mend';

        $responseWithoutSubcamps = $this->getTestApp(false)->handle($this->createRequest($mendUrl));
        self::assertSame(200, $responseWithoutSubcamps->getStatusCode());
        self::assertStringNotContainsString('adminValues', (string)$responseWithoutSubcamps->getBody());

        $participant->subcamp = EventTypeCej::SUBCAMP_SPARTA;
        $participantRepository->persist($participant);
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $response = $this->getTestApp(false)->handle($this->createRequest($mendUrl));
            self::assertSame(200, $response->getStatusCode());
            $body = (string)$response->getBody();
            self::assertStringContainsString('/admin/' . $participant->id . '/adminValues', $body);
            // the stored subcamp is pre-selected in the form
            self::assertStringContainsString('value="' . EventTypeCej::SUBCAMP_SPARTA . '" selected', $body);
            self::assertStringNotContainsString('value="' . EventTypeCej::SUBCAMP_THEBA . '" selected', $body);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testParticipantDashboardShowsAdminValuesWhenSet(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $participantRepository = $this->getService($app, ParticipantRepository::class);

            $suffix = bin2hex(random_bytes(4));
            $participant = $this->createPaidIst($container, 'Dash', 'Board' . $suffix);
            $participant->subcamp = EventTypeCej::SUBCAMP_THEBA;
            $participant->internalUniqueId = 'CEJ-D-' . $suffix;
            $participant->internalCommonId = 'G-D';
            $participantRepository->persist($participant);

            $_SESSION['user'] = ['id' => $participant->getUserButNotNull()->id];

            $response = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/participant/dashboard',
            ));
            self::assertSame(200, $response->getStatusCode());
            $body = (string)$response->getBody();
            self::assertStringContainsString('Théba', $body);
            self::assertStringContainsString('CEJ-D-' . $suffix, $body);
            self::assertStringContainsString('G-D', $body);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testParticipantDashboardHidesAdminValuesWhenUnset(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $participant = $this->createPaidIst($container, 'Empty', 'Values' . bin2hex(random_bytes(4)));
            $_SESSION['user'] = ['id' => $participant->getUserButNotNull()->id];

            $response = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/participant/dashboard',
            ));
            self::assertSame(200, $response->getStatusCode());
            self::assertStringNotContainsString('Internal event values', (string)$response->getBody());
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testDashboardHidesAdminValuesWhenEventHasNoSubcamps(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        // event 4 stays 'default' - no subcamps, so stored values must stay hidden
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);

        $suffix = bin2hex(random_bytes(4));
        $patrolLeader = $this->createPaidPatrolLeader($container, 'Vera', 'Skryta' . $suffix);
        $patrolLeader->subcamp = EventTypeCej::SUBCAMP_THEBA;
        $patrolLeader->internalUniqueId = 'CEJ-H-' . $suffix;
        $patrolLeader->internalCommonId = 'G-H';
        $participantRepository->persist($patrolLeader);

        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->firstName = 'Clenka';
        $patrolParticipant->lastName = 'Skryta' . $suffix;
        $patrolParticipant->subcamp = EventTypeCej::SUBCAMP_THEBA;
        $patrolParticipantRepository->persist($patrolParticipant);

        $_SESSION['user'] = ['id' => $patrolLeader->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringNotContainsString('Internal event values', $body);
        self::assertStringNotContainsString(EventTypeCej::SUBCAMP_THEBA, $body);
        self::assertStringNotContainsString('CEJ-H-' . $suffix, $body);
    }

    public function testPatrolRosterShowsMemberSubcamp(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            /** @var PatrolParticipantRepository $patrolParticipantRepository */
            $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);

            $suffix = bin2hex(random_bytes(4));
            $patrolLeader = $this->createPaidPatrolLeader($container, 'Vera', 'Roster' . $suffix);

            $patrolParticipant = new PatrolParticipant();
            $patrolParticipant->patrolLeader = $patrolLeader;
            $patrolParticipant->firstName = 'Clenka';
            $patrolParticipant->lastName = 'Druzin' . $suffix;
            $patrolParticipant->subcamp = EventTypeCej::SUBCAMP_SPARTA;
            $patrolParticipantRepository->persist($patrolParticipant);

            $_SESSION['user'] = ['id' => $patrolLeader->getUserButNotNull()->id];

            $response = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/participant/dashboard',
            ));
            self::assertSame(200, $response->getStatusCode());
            self::assertStringContainsString('Spárta', (string)$response->getBody());
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    private function createEventAdmin(ContainerInterface $container): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = new User();
        $user->event = $eventRepository->get(4);
        $user->role = UserRole::Admin;
        $user->email = 'admin-values-admin-' . bin2hex(random_bytes(6)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }

    private function createPaidPatrolLeader(
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

        $event = $eventRepository->get(4);
        $email = 'admin-values-pl-' . bin2hex(random_bytes(6)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'pl');
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $patrolLeaderRepository->get($participant->id);
    }
}
