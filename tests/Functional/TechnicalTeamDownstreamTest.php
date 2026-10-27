<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Entry\EntryParticipant;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Export\ExportService;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\ParticipantStatisticsService;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeaderRepository;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipant;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class TechnicalTeamDownstreamTest extends AppTestCase
{
    private const string TEAM_NAME = 'Hasiči';
    private const string NO_EVENT_FOOD = 'detail.foodNoEventFood';

    public function testAllParticipantsWithStatusIncludesTtpViaLeader(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader, $members] = $this->createTeam($app, $event, 2);

        $participants = $this->getService($app, ParticipantRepository::class)->getAllParticipantsWithStatus(
            ParticipantRole::all(),
            [UserStatus::Paid],
            $event,
        );
        $idCounts = array_count_values(
            array_map(fn (Participant $participant): int => $participant->id, $participants),
        );

        self::assertSame(1, $idCounts[$leader->id] ?? 0);
        foreach ($members as $member) {
            self::assertSame(1, $idCounts[$member->id] ?? 0);
        }
    }

    public function testComingCountIncludesTechnicalTeamMembersOnce(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $participantService = $this->getService($app, ParticipantService::class);
        $countBefore = $participantService->getParticipantsComingToEventCount($event);

        $this->createTeam($app, $event, 2);

        self::assertSame($countBefore + 3, $participantService->getParticipantsComingToEventCount($event));
    }

    public function testEventCapBlocksTechnicalTeamClose(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $this->createTeam($app, $event, 2);
        $openLeader = $this->createLeader($app, $event, 'Open', UserStatus::Open);
        $participantService = $this->getService($app, ParticipantService::class);

        $this->mutateEventForTest($app->getContainer(), $event, [
            'maximalClosedParticipantsCount' => $participantService->getParticipantsComingToEventCount($event),
        ]);
        $freshLeader = $this->getService($app, TechnicalTeamLeaderRepository::class)->get($openLeader->id);

        $result = $participantService->isCloseRegistrationValid($freshLeader);

        self::assertContains('flash.warning.fullRegistration', array_column($result->warnings, 'key'));
    }

    public function testPatrolStatisticUnaffectedByTechnicalTeams(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);
        $statisticBefore = $statisticsService->getStatistic($event, ParticipantRole::PatrolLeader);

        $this->createTeam($app, $event, 2);

        self::assertEquals($statisticBefore, $statisticsService->getStatistic($event, ParticipantRole::PatrolLeader));
    }

    public function testDigestFoodStatisticCountsNoEventFood(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $countBefore = $participantRepository->getDigestFoodStatistic($event)[self::NO_EVENT_FOOD] ?? 0;

        $this->createTeam($app, $event, 2);

        self::assertSame(
            $countBefore + 2,
            $participantRepository->getDigestFoodStatistic($event)[self::NO_EVENT_FOOD] ?? 0,
        );
    }

    public function testEntryParticipantsGroupTtpUnderTtl(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader] = $this->createTeam($app, $event, 2);

        $entryParticipants = $this->getService($app, ParticipantRepository::class)
            ->getParticipantsForEntry($event, true);

        $entryLeader = $entryParticipants['ttl'][$leader->id] ?? null;
        self::assertInstanceOf(EntryParticipant::class, $entryLeader);
        self::assertCount(2, $entryLeader->participants);
    }

    public function testBadgesIncludeTechnicalTeamRoles(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader, $members] = $this->createTeam($app, $event, 2);
        $teamIds = [$leader->id, ...array_map(fn (TechnicalTeamParticipant $member): int => $member->id, $members)];

        $badgeParticipants = $this->getService($app, ParticipantRepository::class)->getParticipantsForBadges(
            $event,
            [ParticipantRole::TechnicalTeamLeader, ParticipantRole::TechnicalTeamParticipant],
        );
        $badgeIds = array_map(fn (Participant $participant): int => $participant->id, $badgeParticipants);
        $leaderPosition = array_search($leader->id, $badgeIds, true);

        self::assertIsInt($leaderPosition);
        $teamSlice = array_slice($badgeIds, $leaderPosition, 3);
        self::assertSame($leader->id, $teamSlice[0]);
        self::assertEqualsCanonicalizing($teamIds, $teamSlice);
    }

    public function testFullExportHasTeamColumns(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader, $members] = $this->createTeam($app, $event, 2);

        $rows = $this->getService($app, ExportService::class)
            ->allRegistrationDataToCSV($event, $this->createAdminUser($app));
        $header = $rows[0];
        $idColumn = array_search('id', $header, true);
        $leaderIdColumn = array_search('patrolOrTroopLeaderId', $header, true);
        $teamNameColumn = array_search('patrolName', $header, true);
        $countColumn = array_search('patrolParticipantCount', $header, true);
        self::assertIsInt($idColumn);
        self::assertIsInt($leaderIdColumn);
        self::assertIsInt($teamNameColumn);
        self::assertIsInt($countColumn);

        $rowsById = [];
        foreach ($rows as $row) {
            $rowsById[$row[$idColumn]] = $row;
        }

        $leaderRow = $rowsById[(string)$leader->id] ?? null;
        self::assertIsArray($leaderRow);
        self::assertSame(self::TEAM_NAME, $leaderRow[$teamNameColumn]);
        self::assertSame('2', $leaderRow[$countColumn]);
        foreach ($members as $member) {
            $memberRow = $rowsById[(string)$member->id] ?? null;
            self::assertIsArray($memberRow);
            self::assertSame((string)$leader->id, $memberRow[$leaderIdColumn]);
            self::assertSame(self::TEAM_NAME, $memberRow[$teamNameColumn]);
        }
    }

    public function testCancelTechnicalTeamParticipantIsRejected(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader, $members] = $this->createTeam($app, $event, 1);
        $this->loginAdmin($app, $event);

        $memberFlashes = $this->postAsAdmin($event, '/admin/changeRole/' . $members[0]->id . '/cancel', []);
        $leaderFlashes = $this->postAsAdmin($event, '/admin/changeRole/' . $leader->id . '/cancel', []);

        $translator = $this->getService($this->getTestApp(false), TranslatorInterface::class);
        self::assertContains(
            $translator->trans('flash.warning.cancelTechnicalTeamParticipantNotSupported'),
            $memberFlashes,
        );
        self::assertContains($translator->trans('flash.warning.cancelTechnicalTeamLeaderNotSupported'), $leaderFlashes);
        self::assertSame(UserStatus::Paid, $this->getFreshUser($leader->getUserButNotNull())->status);
    }

    public function testChangeRoleOffersTechnicalTeamLeaderButNotMember(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createLeader($app, $event, 'Adam', UserStatus::Open);
        $this->loginAdmin($app, $event);

        $body = $this->getAdminPage('/v2/event/' . $event->slug . '/admin/changeRole/' . $leader->id . '/show');

        self::assertStringContainsString('value="ttl"', $body);
        self::assertStringNotContainsString('value="ttp"', $body);
    }

    public function testPaidLeaderWithoutPaymentCannotDownloadReceipt(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createLeader($app, $event, 'Adam', UserStatus::Paid);
        $_SESSION['user'] = ['id' => $leader->getUserButNotNull()->id];

        $dashboard = $this->getAdminPage('/v2/event/' . $event->slug . '/participant/dashboard');
        $receipt = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/download/receipt'),
        );

        self::assertStringNotContainsString('download/receipt', $dashboard);
        self::assertSame(302, $receipt->getStatusCode());
        self::assertStringContainsString('dashboard', $receipt->getHeaderLine('Location'));
    }

    public function testPaidLeaderWithPaymentCanDownloadReceipt(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createLeader($app, $event, 'Adam', UserStatus::Paid);
        $this->createPaidPayment($app, $leader);
        $_SESSION['user'] = ['id' => $leader->getUserButNotNull()->id];

        $dashboard = $this->getAdminPage('/v2/event/' . $event->slug . '/participant/dashboard');
        $receipt = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/download/receipt'),
        );

        self::assertStringContainsString('download/receipt', $dashboard);
        self::assertSame(200, $receipt->getStatusCode());
        self::assertSame('application/pdf', $receipt->getHeaderLine('Content-Type'));
    }

    public function testReceiptWithAddressStillRenders(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createLeader($app, $event, 'Adam', UserStatus::Paid);
        $leader->permanentResidence = 'Dlouhá 1, Praha';
        $this->getService($app, TechnicalTeamLeaderRepository::class)->persist($leader);
        $this->createPaidPayment($app, $leader);
        $_SESSION['user'] = ['id' => $leader->getUserButNotNull()->id];

        $receipt = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/download/receipt'),
        );

        self::assertSame(200, $receipt->getStatusCode());
        self::assertSame('application/pdf', $receipt->getHeaderLine('Content-Type'));
    }

    public function testAdminCannotAddPaymentToTtp(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [, $members] = $this->createTeam($app, $event, 1);
        $this->loginAdmin($app, $event);

        $flashes = $this->postAsAdmin(
            $event,
            '/admin/payments/addNewPayment/' . $members[0]->id,
            ['price' => '100', 'reason' => 'test'],
        );

        $translator = $this->getService($this->getTestApp(false), TranslatorInterface::class);
        self::assertContains($translator->trans('flash.warning.technicalTeamParticipantCannotHavePayment'), $flashes);
        self::assertSame([], $this->getService($app, PaymentRepository::class)->findByParticipant($members[0]));
    }

    public function testPaymentsAdminListsTechnicalTeamLeaders(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['technicalTeamPrice' => 100]);
        $leader = $this->createLeader($app, $event, 'Payable', UserStatus::Closed);
        $this->addMember($app, $leader, 'Payable');
        $this->initializeMailerSettings($app, $event);
        $freshLeader = $this->getService($app, TechnicalTeamLeaderRepository::class)->get($leader->id);

        $this->getService($app, ParticipantService::class)->approveRegistration($freshLeader);

        $payments = $this->getService($app, PaymentRepository::class)->findByParticipant($freshLeader);
        self::assertCount(1, $payments);
        self::assertSame('200', $payments[0]->price);

        $this->loginAdmin($app, $event);
        $response = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/admin/payments'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString($freshLeader->getFullName(), (string)$response->getBody());
    }

    public function testFoodPlanSkipsNoEventFood(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $this->createTeam($app, $event, 2);

        $csvRows = $this->getService($app, ParticipantStatisticsService::class)
            ->createParticipantFoodPlanFromEvent($event, false)
            ->aggregatedToCSV();

        self::assertNotContains(self::NO_EVENT_FOOD, $csvRows[1]);
        $leaderRows = array_values(array_filter(
            $csvRows,
            fn (array $row): bool => $row[0] === 'role.ttl',
        ));
        self::assertCount(1, $leaderRows);
        $leaderMealsCount = array_sum(array_map(intval(...), array_slice($leaderRows[0], 1)));
        self::assertGreaterThan(0, $leaderMealsCount);
    }

    public function testPaymentTransferRejectsTechnicalTeamRoles(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$leader, $members] = $this->createTeam($app, $event, 1);
        $otherLeader = $this->createLeader($app, $event, 'Other', UserStatus::Approved);
        $transferService = $this->getService($app, PaymentTransferService::class);
        $flash = $this->getService($app, FlashMessagesBySession::class);

        $translator = $this->getService($app, TranslatorInterface::class);

        $flash->dumpMessagesIntoArray();

        self::assertFalse($transferService->isPaymentTransferPossible($members[0], $otherLeader, $flash));
        $memberMessages = array_column($flash->dumpMessagesIntoArray(), 'message');
        self::assertContains($translator->trans('flash.warning.groupMemberNotSupported'), $memberMessages);
        self::assertNotContains($translator->trans('flash.warning.patrolLeaderNotSupported'), $memberMessages);
        self::assertNotContains($translator->trans('flash.warning.technicalTeamLeaderNotSupported'), $memberMessages);

        self::assertFalse($transferService->isPaymentTransferPossible($leader, $otherLeader, $flash));
        $leaderMessages = array_column($flash->dumpMessagesIntoArray(), 'message');
        self::assertContains($translator->trans('flash.warning.technicalTeamLeaderNotSupported'), $leaderMessages);
        self::assertNotContains($translator->trans('flash.warning.patrolLeaderNotSupported'), $leaderMessages);
        self::assertNotContains($translator->trans('flash.warning.groupMemberNotSupported'), $leaderMessages);
    }

    public function testAdminPagesRenderTechnicalTeams(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        [$paidLeader, $paidMembers] = $this->createTeam($app, $event, 1);
        $openLeader = $this->createLeader($app, $event, 'Opened', UserStatus::Open);
        $this->addMember($app, $openLeader, 'OpenedMember');
        $closedLeader = $this->createLeader($app, $event, 'Closed', UserStatus::Closed);
        $this->addMember($app, $closedLeader, 'ClosedMember');
        $this->loginAdmin($app, $event);

        $adminUrl = '/v2/event/' . $event->slug . '/admin';
        self::assertStringContainsString('Zbyšek1', $this->getAdminPage($adminUrl . '/showPaid'));
        $openPage = $this->getAdminPage($adminUrl . '/showOpen');
        self::assertStringContainsString('OpenedMember', $openPage);
        self::assertStringContainsString('Počet lidí v týmu', $openPage);
        self::assertStringNotContainsString('v patrole', $openPage);
        self::assertStringContainsString('ClosedMember', $this->getAdminPage($adminUrl . '/approving'));
        self::assertStringContainsString(
            $this->getService($app, TranslatorInterface::class)->trans('dashboard-admin.paidTtl'),
            $this->getAdminPage($adminUrl . '/dashboard'),
        );
        $leaderMendPage = $this->getAdminPage($adminUrl . '/' . $paidLeader->id . '/mend');
        self::assertStringContainsString(self::TEAM_NAME, $leaderMendPage);
        self::assertStringContainsString($paidMembers[0]->getFullName(), $leaderMendPage);
        self::assertStringContainsString($adminUrl . '/' . $paidMembers[0]->id . '/mend', $leaderMendPage);
        self::assertStringNotContainsString(
            '/changeRole/' . $paidMembers[0]->id . '/cancel',
            $this->getAdminPage($adminUrl . '/' . $paidMembers[0]->id . '/mend'),
        );
        $this->getAdminPage($adminUrl . '/' . $paidMembers[0]->id . '/showDetails');
    }

    /**
     * @param App<ContainerInterface> $app
     * @param array<string, mixed> $extraFields
     */
    private function prepareEvent(App $app, array $extraFields = []): Event
    {
        $event = $this->getNavigamusTestEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTechnicalTeams' => true,
            'skautisAppId' => 'downstream-test-app-id',
            ...$extraFields,
        ]);

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return array{0: TechnicalTeamLeader, 1: list<TechnicalTeamParticipant>}
     */
    private function createTeam(App $app, Event $event, int $membersCount): array
    {
        $leader = $this->createLeader($app, $event, 'Adam', UserStatus::Paid);
        $members = [];
        for ($i = 1; $i <= $membersCount; $i++) {
            $members[] = $this->addMember($app, $leader, 'Zbyšek' . $i);
        }

        return [$this->getService($app, TechnicalTeamLeaderRepository::class)->get($leader->id), $members];
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createLeader(App $app, Event $event, string $firstName, UserStatus $status): TechnicalTeamLeader
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('ttl-downstream-' . bin2hex(random_bytes(6)) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ttl');

        $repository = $this->getService($app, TechnicalTeamLeaderRepository::class);
        $leader = $repository->get($participant->id);
        $leader->firstName = $firstName;
        $leader->lastName = 'Leader';
        $leader->technicalTeamName = self::TEAM_NAME;
        $leader->foodPreferences = 'detail.foodWithout';
        $leader->scarf = Participant::SCARF_YES;
        $repository->persist($leader);

        if ($status === UserStatus::Paid) {
            $userService->setUserPaid($user);
        } elseif ($status === UserStatus::Closed) {
            $userService->setUserClosed($user);
        } elseif ($status === UserStatus::Approved) {
            $userService->setUserApproved($user);
        }

        return $repository->get($leader->id);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function addMember(App $app, TechnicalTeamLeader $leader, string $firstName): TechnicalTeamParticipant
    {
        $member = new TechnicalTeamParticipant();
        $member->technicalTeamLeader = $leader;
        $member->firstName = $firstName;
        $member->lastName = 'Member';
        $member->foodPreferences = self::NO_EVENT_FOOD;
        $member->scarf = Participant::SCARF_YES;
        $this->getService($app, TechnicalTeamParticipantRepository::class)->persist($member);

        return $member;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidPayment(App $app, TechnicalTeamLeader $leader): void
    {
        $payment = new Payment();
        $payment->participant = $leader;
        $payment->status = PaymentStatus::Paid;
        $payment->price = '1200';
        $payment->currency = 'Kč';
        $payment->variableSymbol = (string)random_int(1000000000, 9999999999);
        $payment->purpose = 'event fee';
        $payment->accountNumber = '';
        $payment->iban = '';
        $payment->swift = '';
        $payment->constantSymbol = '';
        $payment->note = '';
        $payment->due = DateTimeUtils::getDateTime('+14 days');
        $payment->paidAt = DateTimeUtils::getDateTime('-1 day');
        $this->getService($app, PaymentRepository::class)->persist($payment);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function loginAdmin(App $app, Event $event): void
    {
        $adminUser = $this->createAdminUser($app);
        $adminUser->event = $event;
        $this->getService($app, UserRepository::class)->persist($adminUser);
        $_SESSION['user'] = ['id' => $adminUser->id];
    }

    /**
     * @param array<string, string> $body
     * @return list<string>
     */
    private function postAsAdmin(Event $event, string $path, array $body): array
    {
        $app = $this->getTestApp(false);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . $path, 'POST', $body));
        self::assertSame(302, $response->getStatusCode());

        return array_values(array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        ));
    }

    private function getAdminPage(string $path): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest($path));
        self::assertSame(200, $response->getStatusCode(), $path);

        return (string)$response->getBody();
    }

    private function getFreshUser(User $user): User
    {
        return $this->getService($this->getTestApp(false), UserRepository::class)->get($user->id);
    }
}
