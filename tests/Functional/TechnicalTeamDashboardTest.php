<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\RoleChangeResult;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeaderRepository;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipant;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class TechnicalTeamDashboardTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testDashboardShowsAddMemberButton(): void
    {
        $app = $this->getTestApp();
        [, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-dash-1@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(self::BASE_URL . '/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('/technical-team/addParticipant', (string)$response->getBody());
    }

    public function testAddMemberPersistsOnlyAllowedFields(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-add-1@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/addParticipant',
            'POST',
            [
                'firstName' => 'Ann',
                'lastName' => 'Bell',
                'foodPreferences' => 'detail.foodNoEventFood',
                'arrivalDate' => '2027-07-01',
                'departureDate' => '2027-07-03',
                'healthProblems' => 'x',
            ],
        ));

        self::assertSame(302, $response->getStatusCode());
        $members = $this->getMembers($leader);
        self::assertCount(1, $members);
        self::assertSame('detail.foodNoEventFood', $members[0]->foodPreferences);
        self::assertNull($members[0]->healthProblems);
    }

    public function testLeaderSavesTeamNameFromDetailsForm(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-team-name@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/participant/changeDetails',
            'POST',
            ['technicalTeamName' => 'Kitchen crew'],
        ));

        self::assertSame(302, $response->getStatusCode());
        $freshLeader = $this->getService($this->getTestApp(false), TechnicalTeamLeaderRepository::class)
            ->get($leader->id);
        self::assertSame('Kitchen crew', $freshLeader->technicalTeamName);
    }

    public function testNonLeaderIsRedirectedFromTechnicalTeamRoutes(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$leader, $leaderUser] = $this->createTechnicalTeamLeader($container, 'ttl-only-leader@example.com');
        $event = $leaderUser->event;
        $this->mutateEventForTest($container, $event, ['allowIsts' => true]);
        $istUser = $this->getService($app, UserService::class)->registerEmailUser('ttl-only-ist@example.com', $event);
        $this->getService($app, UserService::class)->createParticipantSetRole($istUser, 'ist');
        $_SESSION['user'] = ['id' => $istUser->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/addParticipant',
            'POST',
            ['firstName' => 'Intruder'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringEndsWith('/v2/event/' . self::TEST_EVENT_SLUG, $response->getHeaderLine('Location'));
        self::assertContains(
            $this->getService($app, TranslatorInterface::class)->trans('flash.error.ttlOnly'),
            array_column($this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(), 'message'),
        );
        self::assertSame([], $this->getMembers($leader));
    }

    public function testCannotEditForeignTeamMember(): void
    {
        $app = $this->getTestApp();
        [, $userA] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-foreign-a@example.com');
        [$leaderB] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-foreign-b@example.com');
        $memberB = $this->addMember($leaderB, 'Original');
        $_SESSION['user'] = ['id' => $userA->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $memberB->id . '/changeDetails',
            'POST',
            ['firstName' => 'Hacked'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));
        self::assertSame('Original', $this->getMembers($leaderB)[0]->firstName);
    }

    public function testCloseWithIncompleteMemberIsRejected(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-close-1@example.com');
        $this->prepareClosableLeader($leader);
        $this->addMember($leader, 'NoFood');
        $_SESSION['user'] = ['id' => $user->id];

        $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/closeRegistration',
            'POST',
        ));

        self::assertSame(UserStatus::Open, $this->getFreshUser($user)->status);
    }

    public function testCloseWithZeroMembersAndCompleteLeaderSucceeds(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-close-2@example.com');
        $this->prepareClosableLeader($leader);
        $_SESSION['user'] = ['id' => $user->id];

        $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/closeRegistration',
            'POST',
        ));

        self::assertSame(UserStatus::Closed, $this->getFreshUser($user)->status);
    }

    public function testEmptyStringFoodCountsAsMissingForClose(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-close-3@example.com');
        $this->prepareClosableLeader($leader);
        $this->addMember($leader, 'EmptyFood', function (TechnicalTeamParticipant $member): void {
            $member->foodPreferences = '';
            $member->arrivalDate = DateTimeUtils::getDateTime('2027-07-01');
            $member->departureDate = DateTimeUtils::getDateTime('2027-07-03');
        });
        $_SESSION['user'] = ['id' => $user->id];

        $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/closeRegistration',
            'POST',
        ));

        self::assertSame(UserStatus::Open, $this->getFreshUser($user)->status);
    }

    public function testApproveZeroPriceTeamGoesStraightToPaid(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$leader, $user] = $this->createTechnicalTeamLeader($container, 'ttl-approve-1@example.com');
        $this->addMember($leader, 'Member');
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($container, $event, ['technicalTeamPrice' => null]);
        $this->initializeMailerSettings($app, $event);
        $this->getService($app, UserService::class)->setUserClosed($user);
        $freshLeader = $this->getService($app, TechnicalTeamLeaderRepository::class)->get($leader->id);

        $this->getService($app, ParticipantService::class)->approveRegistration($freshLeader);

        self::assertSame(UserStatus::Paid, $this->getFreshUser($user)->status);
        self::assertSame([], $this->getService($app, PaymentRepository::class)->findByParticipant($freshLeader));
    }

    public function testRoleChangeBetweenIstAndTechnicalTeamLeaderSucceeds(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$leader, $leaderUser] = $this->createTechnicalTeamLeader($container, 'ttl-role-1@example.com');
        $event = $leaderUser->event;
        $this->mutateEventForTest($container, $event, ['allowIsts' => true]);
        $istUser = $this->getService($app, UserService::class)->registerEmailUser('ttl-role-ist@example.com', $event);
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($istUser, 'ist');
        $participantService = $this->getService($app, ParticipantService::class);

        self::assertSame(RoleChangeResult::Success, $participantService->tryChangeRole('ttl', $ist, $event));
        self::assertSame(RoleChangeResult::Success, $participantService->tryChangeRole('ist', $leader, $event));
        self::assertInstanceOf(
            TechnicalTeamLeader::class,
            $this->getService($this->getTestApp(false), ParticipantRepository::class)->get($ist->id),
        );
    }

    public function testTechnicalTeamLeaderWithMembersCannotChangeRole(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$leader, $leaderUser] = $this->createTechnicalTeamLeader($container, 'ttl-role-2@example.com');
        $event = $leaderUser->event;
        $this->mutateEventForTest($container, $event, ['allowIsts' => true]);
        $this->addMember($leader, 'Member');
        $freshLeader = $this->getService($this->getTestApp(false), TechnicalTeamLeaderRepository::class)
            ->get($leader->id);

        self::assertSame(
            RoleChangeResult::TechnicalTeamHasParticipants,
            $this->getService($app, ParticipantService::class)->tryChangeRole('ist', $freshLeader, $event),
        );
    }

    public function testRoleChangeToOrFromTechnicalTeamParticipantIsRejected(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$leader, $leaderUser] = $this->createTechnicalTeamLeader($container, 'ttl-role-3@example.com');
        $event = $leaderUser->event;
        $this->mutateEventForTest($container, $event, ['allowIsts' => true]);
        $member = $this->addMember($leader, 'Member');
        $istUser = $this->getService($app, UserService::class)->registerEmailUser('ttl-role-ist-3@example.com', $event);
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($istUser, 'ist');
        $participantService = $this->getService($app, ParticipantService::class);

        self::assertSame(RoleChangeResult::RoleNotValid, $participantService->tryChangeRole('ttp', $ist, $event));
        self::assertSame(RoleChangeResult::RoleNotValid, $participantService->tryChangeRole('ist', $member, $event));
    }

    public function testClosedLeaderCanEditMemberDatesOnly(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-lock-1@example.com');
        $member = $this->addMember($leader, 'Original');
        $this->getService($app, UserService::class)->setUserClosed($user);
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $member->id . '/changeDetailsAfterLock',
            'POST',
            ['arrivalDate' => '2027-07-02', 'departureDate' => '2027-07-04', 'firstName' => 'Changed'],
        ));

        self::assertSame(302, $response->getStatusCode());
        $reloaded = $this->getMembers($leader)[0];
        self::assertSame('2027-07-02', $reloaded->arrivalDate?->format('Y-m-d'));
        self::assertSame('2027-07-04', $reloaded->departureDate?->format('Y-m-d'));
        self::assertSame('Original', $reloaded->firstName);
    }

    public function testOpenLeaderCannotUseAfterLockMemberEdit(): void
    {
        $app = $this->getTestApp();
        $this->expectLog(Level::Warning, 'tried to access after-lock edit');
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-lock-2@example.com');
        $member = $this->addMember($leader, 'Original');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $member->id . '/changeDetailsAfterLock',
            'POST',
            ['arrivalDate' => '2027-07-02', 'departureDate' => '2027-07-04', 'firstName' => 'Changed'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringEndsWith(self::BASE_URL . '/participant/dashboard', $response->getHeaderLine('Location'));
        self::assertContains(
            $this->getService($app, TranslatorInterface::class)->trans('flash.error.lockedStatusRequired'),
            array_column($this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(), 'message'),
        );

        $reloaded = $this->getMembers($leader)[0];
        self::assertNull($reloaded->arrivalDate);
        self::assertSame('Original', $reloaded->firstName);
    }

    public function testPaidLeaderDashboardOffersNoOwnerTicketTransfer(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, ['allowTechnicalTeams' => true]);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('ttl-transfer-' . bin2hex(random_bytes(4)) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ttl');
        $userService->setUserPaid($user);
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/dashboard'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('/showTransferTicket', (string)$response->getBody());
    }

    public function testCannotDeleteForeignTeamMember(): void
    {
        $app = $this->getTestApp();
        [, $userA] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-del-a@example.com');
        [$leaderB] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-del-b@example.com');
        $memberB = $this->addMember($leaderB, 'Original');
        $_SESSION['user'] = ['id' => $userA->id];

        $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $memberB->id . '/delete',
            'POST',
        ));

        self::assertCount(1, $this->getMembers($leaderB));
    }

    public function testClosedLeaderCannotDeleteOrEditMember(): void
    {
        $app = $this->getTestApp();
        $this->expectLog(Level::Warning, 'is trying to change data');
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-closed-del@example.com');
        $member = $this->addMember($leader, 'Original');
        $this->getService($app, UserService::class)->setUserClosed($user);
        $_SESSION['user'] = ['id' => $user->id];

        foreach ([['/delete', []], ['/changeDetails', ['firstName' => 'Changed']]] as [$path, $body]) {
            $response = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/technical-team/participant/' . $member->id . $path,
                'POST',
                $body,
            ));

            self::assertSame(302, $response->getStatusCode(), $path);
            self::assertStringEndsWith(
                self::BASE_URL . '/participant/dashboard',
                $response->getHeaderLine('Location'),
                $path,
            );
            self::assertContains(
                $this->translate($app, 'flash.error.openStatusRequired'),
                $this->flashed($app),
                $path,
            );
        }

        $members = $this->getMembers($leader);
        self::assertCount(1, $members);
        self::assertSame('Original', $members[0]->firstName);
    }

    public function testShowDeleteOnOwnLeaderIdRedirectsToDashboard(): void
    {
        $app = $this->getTestApp();
        [$leader, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-wrong-id-1@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $leader->id . '/showDelete',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));
    }

    public function testShowDeleteOnNonexistentIdRedirectsToDashboard(): void
    {
        $app = $this->getTestApp();
        [, $user] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-wrong-id-2@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/2000000000/showDelete',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));
    }

    public function testCannotEditForeignTeamMemberAfterLock(): void
    {
        $app = $this->getTestApp();
        [, $userA] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-lockf-a@example.com');
        [$leaderB] = $this->createTechnicalTeamLeader($app->getContainer(), 'ttl-lockf-b@example.com');
        $memberB = $this->addMember($leaderB, 'Original');
        $this->getService($app, UserService::class)->setUserClosed($userA);
        $_SESSION['user'] = ['id' => $userA->id];

        $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/technical-team/participant/' . $memberB->id . '/changeDetailsAfterLock',
            'POST',
            ['arrivalDate' => '2027-07-02', 'departureDate' => '2027-07-04'],
        ));

        $reloaded = $this->getMembers($leaderB)[0];
        self::assertNull($reloaded->arrivalDate);
        self::assertNull($reloaded->departureDate);
    }

    /**
     * @return array{0: TechnicalTeamLeader, 1: User}
     */
    private function createTechnicalTeamLeader(ContainerInterface $container, string $email): array
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($container, $event, [
            'allowTechnicalTeams' => true,
            'startRegistration' => DateTimeUtils::getDateTime('-1 day'),
        ]);

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ttl');

        /** @var TechnicalTeamLeaderRepository $repository */
        $repository = $container->get(TechnicalTeamLeaderRepository::class);

        return [$repository->get($participant->id), $user];
    }

    private function prepareClosableLeader(TechnicalTeamLeader $leader): void
    {
        $leader->technicalTeamName = 'Kitchen';
        $leader->firstName = 'Lead';
        $leader->lastName = 'Er';
        $leader->email = 'ttl-leader-contact@example.com';
        $leader->telephoneNumber = '+420123456789';
        $leader->foodPreferences = 'detail.foodNoEventFood';
        $leader->arrivalDate = DateTimeUtils::getDateTime('2027-07-01');
        $leader->departureDate = DateTimeUtils::getDateTime('2027-07-03');
        /** @var TechnicalTeamLeaderRepository $repository */
        $repository = $this->getTestApp(false)->getContainer()->get(TechnicalTeamLeaderRepository::class);
        $repository->persist($leader);
    }

    /**
     * @param (callable(TechnicalTeamParticipant): mixed)|null $fill
     */
    private function addMember(
        TechnicalTeamLeader $leader,
        string $firstName,
        ?callable $fill = null,
    ): TechnicalTeamParticipant {
        $member = new TechnicalTeamParticipant();
        $member->technicalTeamLeader = $leader;
        $member->firstName = $firstName;
        $member->lastName = 'Member';
        if ($fill !== null) {
            $fill($member);
        }
        /** @var TechnicalTeamParticipantRepository $repository */
        $repository = $this->getTestApp(false)->getContainer()->get(TechnicalTeamParticipantRepository::class);
        $repository->persist($member);

        return $member;
    }

    /**
     * @return list<TechnicalTeamParticipant>
     */
    private function getMembers(TechnicalTeamLeader $leader): array
    {
        /** @var TechnicalTeamParticipantRepository $repository */
        $repository = $this->getTestApp(false)->getContainer()->get(TechnicalTeamParticipantRepository::class);

        return array_values($repository->findAllForTechnicalTeamLeader($leader));
    }

    private function getFreshUser(User $user): User
    {
        /** @var UserRepository $repository */
        $repository = $this->getTestApp(false)->getContainer()->get(UserRepository::class);

        return $repository->get($user->id);
    }
}
