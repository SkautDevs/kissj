<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Settings\TwigExtension;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class OwnerTransferTicketPageRenderTest extends AppTestCase
{
    // getOwnerTransferEvent() always clones/reuses the obrok37 fixture
    private const string TEST_EVENT_SLUG = 'obrok37';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testTransferPageRendersTieCodeForm(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'One');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('name="tieCode"', $body);
        self::assertStringNotContainsString('name="emailTo"', $body);
    }

    public function testTransferPagePreviewShowsRecipientNameAndStatus(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Two');
        $recipient = $this->createIst($app, $event, 'transfer-recipient-' . uniqid('', true) . '@example.com', UserStatus::Approved, 'Pavel', 'Recipient');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $recipientParticipant = $participantRepository->getParticipantFromUser($recipient);
        $recipientCode = $recipientParticipant->tieCode;

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => $recipientCode]));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('Pavel', $body);
        self::assertStringContainsString('Recipient', $body);

        $translator = $this->getService($app, TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('dashboard.userStatus.approved'),
            $body,
        );
    }

    public function testTransferPageShowsNotFoundForUnknownCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Three');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => 'ZZZZZZ']));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $translator = $this->getService($app, TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('ticketTransfer.recipientNotFound'),
            $body,
        );
        // the admin-flow "one or both participants not found" flash is misleading here
        self::assertStringNotContainsString(
            $translator->trans('flash.warning.nullParticipants'),
            $body,
        );
        self::assertStringContainsString(
            $translator->trans('flash.warning.transferRecipientNotFound'),
            $body,
        );
    }

    public function testTransferPageShowsNotPossibleForIneligibleRecipient(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Four');
        $recipient = $this->createIst($app, $event, 'transfer-recipient-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Pavel', 'Recipient');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $recipientCode = $participantRepository->getParticipantFromUser($recipient)->tieCode;

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => $recipientCode]));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $translator = $this->getService($app, TranslatorInterface::class);
        self::assertStringContainsString('Pavel', $body);
        self::assertStringContainsString($translator->trans('ticketTransfer.notPossible'), $body);
        // recipient is already Paid — the GET preview must surface the detailed reason, unlike the POST
        self::assertStringContainsString($translator->trans('flash.warning.isPaid'), $body);
    }

    public function testShowTransferTicketBlocksNonPaidGiver(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        // Approved, not Paid — PaidStatusOnlyMiddleware must block before the controller ever runs
        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Approved, 'Giver', 'Five');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/showTransferTicket'
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));
        self::assertContains(
            $this->getService($app, TranslatorInterface::class)->trans('flash.error.paidStatusRequired'),
            $this->flashed($app),
        );
    }

    public function testShowTransferTicketBlocksAfterEventStart(): void
    {
        $app = $this->getTestApp();
        // flip the seeded event's type so the event-type check passes, but keep its seeded
        // 2021-01-01 startDay so only OwnerTicketTransferAllowedOnlyMiddleware's startDay check trips
        $this->setEventType($app->getContainer(), 'obrok', 'test-event-slug');
        $event = $this->getService($app, EventRepository::class)->get(1);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Six');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/showTransferTicket'
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));
        self::assertContains(
            $this->getService($app, TranslatorInterface::class)->trans('flash.error.ownerTransferAfterEventStart'),
            $this->flashed($app),
        );
    }

    public function testDashboardOffersTransferLinkBeforeEventStart(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Seven');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            $this->getService($app, TranslatorInterface::class)->trans('ticketTransfer.dashboardLink'),
            (string) $response->getBody(),
        );
    }

    public function testDashboardHidesTransferLinkAfterEventStart(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'startDay' => DateTimeUtils::getDateTime('-1 day'),
        ]);

        $giver = $this->createIst($app, $event, 'transfer-giver-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Giver', 'Eight');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        // the middleware would bounce the link anyway, so the dashboard must stop offering it
        self::assertStringNotContainsString(
            $this->getService($app, TranslatorInterface::class)->trans('ticketTransfer.dashboardLink'),
            (string) $response->getBody(),
        );
    }

    public function testEligibleForShowTieCodeTrueForApprovedIstOnOwnerTransferEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $recipient = $this->createIst($app, $event, 'transfer-eligible-' . uniqid('', true) . '@example.com', UserStatus::Approved, 'Approved', 'Recipient');
        $participant = $this->getService($app, ParticipantRepository::class)->getParticipantFromUser($recipient);

        self::assertTrue($this->eligibleForShowTieCode($app, $participant));
    }

    public function testEligibleForShowTieCodeFalseForPaidIstOnOwnerTransferEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        // Paid participants have already received a ticket, so their code must stay hidden
        // even on an owner-transfer event - proves the clause checks status, not just event type
        $giver = $this->createIst($app, $event, 'transfer-eligible-' . uniqid('', true) . '@example.com', UserStatus::Paid, 'Paid', 'Giver');
        $participant = $this->getService($app, ParticipantRepository::class)->getParticipantFromUser($giver);

        self::assertFalse($this->eligibleForShowTieCode($app, $participant));
    }

    public function testEligibleForShowTieCodeFalseForTroopParticipantWithLeaderOnOwnerTransferEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $userService = $this->getService($app, UserService::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);

        $leaderUser = $userService->registerEmailUser('transfer-leader-' . uniqid('', true) . '@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'tl');
        $troopLeader = $troopLeaderRepository->get($leaderParticipant->id);

        $participantUser = $userService->registerEmailUser('transfer-tp-' . uniqid('', true) . '@example.com', $event);
        $participantParticipant = $userService->createParticipantSetRole($participantUser, 'tp');
        $troopParticipant = $troopParticipantRepository->get($participantParticipant->id);
        $troopParticipant->troopLeader = $troopLeader;
        $troopParticipantRepository->persist($troopParticipant);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantUser->status = UserStatus::Approved;
        $userRepository->persist($participantUser);

        /** @var TroopParticipant $reloaded */
        $reloaded = $troopParticipantRepository->get($troopParticipant->id);
        self::assertFalse($this->eligibleForShowTieCode($app, $reloaded));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function eligibleForShowTieCode(App $app, Participant $participant): bool
    {
        $twigExtension = $this->getService($app, TwigExtension::class);
        foreach ($twigExtension->getTests() as $test) {
            if ($test->getName() === 'eligibleForShowTieCode') {
                $callable = $test->getCallable();
                self::assertIsCallable($callable);
                $result = $callable($participant);
                self::assertIsBool($result);

                return $result;
            }
        }

        throw new LogicException('eligibleForShowTieCode test not registered');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, Event $event, string $email, UserStatus $status, string $firstName, string $lastName): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        /** @var Ist $ist */
        $ist = $istRepository->get($participant->id);
        $ist->firstName = $firstName;
        $ist->lastName = $lastName;
        $istRepository->persist($ist);

        $userRepository = $this->getService($app, UserRepository::class);
        $user->status = $status;
        $userRepository->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<string>
     */
    private function flashed(App $app): array
    {
        return array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
    }
}
