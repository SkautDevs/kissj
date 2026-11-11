<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\Mailer;
use kissj\Participant\Admin\AdminPaymentController;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeService;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class AdminPaymentTransferTest extends AppTestCase
{
    public function testTransferPaymentMovesMoneyAndStatusesViaAdminController(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $sentTo = $this->captureSentMessageRecipients($app);

        $controller = $this->getService($app, AdminPaymentController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody([
            'emailFrom' => $giver->email,
            'emailTo' => $recipient->email,
        ]);

        $response = $controller->transferPayment($request, new Response(), $event);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);

        // deferred mail runs only after the commit, and both sides get notified
        self::assertCount(2, $sentTo);
        self::assertContains($giver->email, $sentTo->getArrayCopy());
        self::assertContains($recipient->email, $sentTo->getArrayCopy());
    }

    public function testTransferPaymentFailsGracefullyWhenGiverHasNoPaidPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);

        // Paid status without a backing payment row - isPaymentTransferPossible refuses it before any transfer starts
        $giver = $this->createIst($app, $event);
        $giver->status = UserStatus::Paid;
        $userRepository->persist($giver);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $sentTo = $this->captureSentMessageRecipients($app);

        $controller = $this->getService($app, AdminPaymentController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody([
            'emailFrom' => $giver->email,
            'emailTo' => $recipient->email,
        ]);

        $response = $controller->transferPayment($request, new Response(), $event);

        self::assertSame(302, $response->getStatusCode());

        $translator = $this->getService($app, TranslatorInterface::class);
        $flashed = array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
        self::assertContains($translator->trans('flash.error.transferFailed'), $flashed);

        // the refused transfer left both statuses untouched
        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);

        // nothing was transferred, so nobody was notified
        self::assertCount(0, $sentTo);
    }

    public function testLockInIdOrderLocksLowerIdFirst(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $first = $participantRepository->getParticipantFromUser($this->createIst($app, $event));
        $second = $participantRepository->getParticipantFromUser($this->createIst($app, $event));
        self::assertLessThan($second->id, $first->id);

        $repository = $this->getMockBuilder(ParticipantRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['lockRow'])
            ->getMock();
        $lockedIds = [];
        $repository->expects(self::exactly(2))->method('lockRow')
            ->willReturnCallback(static function (int $id) use (&$lockedIds): void {
                $lockedIds[] = $id;
            });

        $repository->lockInIdOrder($second, $first);

        self::assertSame([$first->id, $second->id], $lockedIds);
    }

    public function testTransferLocksBothParticipantsBeforeStatusChange(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $giver = $this->createIst($app, $event);
        $giver->status = UserStatus::Paid;
        $userRepository->persist($giver);
        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);
        $giverParticipant = $participantRepository->getParticipantFromUser($giver);

        $lockingRepository = $this->createMock(ParticipantRepository::class);
        $recipientParticipant = $participantRepository->getParticipantFromUser($recipient);
        $lockingRepository->expects(self::once())
            ->method('lockInIdOrder')
            ->with($giverParticipant, $recipientParticipant)
            ->willThrowException(new RuntimeException('locked first'));
        $service = new PaymentTransferService(
            $userRepository,
            $lockingRepository,
            $this->getService($app, PaymentRepository::class),
            $this->getService($app, PaymentService::class),
            $this->getService($app, Mailer::class),
            $this->getService($app, LoggerInterface::class),
            $this->getService($app, Metrics::class),
            $this->getService($app, ProgrammeService::class),
        );

        try {
            $service->transferPayment($giverParticipant, $recipientParticipant);
            self::fail('Both participant locks must run before any status change');
        } catch (RuntimeException $exception) {
            self::assertSame('locked first', $exception->getMessage());
        }

        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
    }

    public function testTransferPaymentRollsBackStatusesAndNotifiesNobodyWhenPaymentIsMissing(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $giver = $this->createIst($app, $event);
        $giver->status = UserStatus::Paid;
        $userRepository->persist($giver);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $sentTo = $this->captureSentMessageRecipients($app);

        // straight at the service - the controllers' eligibility gate would refuse this sender first,
        // leaving the rollback path of transferPaymentInner untested
        $thrown = null;
        try {
            $this->getService($app, PaymentTransferService::class)->transferPayment(
                $participantRepository->getParticipantFromUser($giver),
                $participantRepository->getParticipantFromUser($recipient),
            );
        } catch (RuntimeException $exception) {
            $thrown = $exception;
        }

        self::assertNotNull($thrown);
        self::assertStringContainsString('Payment marked as paid was not found', $thrown->getMessage());

        // the claimStatusChange writes were rolled back with the transaction
        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);

        self::assertCount(0, $sentTo);
    }

    public function testAdminTransferStillWorksAfterEventStarted(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->mutateEventForTest($container, $event, [
            'startDay' => DateTimeUtils::getDateTime('-1 day'),
        ]);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $this->captureSentMessageRecipients($app);

        $controller = $this->getService($app, AdminPaymentController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody([
            'emailFrom' => $giver->email,
            'emailTo' => $recipient->email,
        ]);

        // admins reach transferPayment through AdminPaymentController, never through
        // OwnerTicketTransferAllowedOnlyMiddleware, so the start-day cut-off must not apply
        $response = $controller->transferPayment($request, new Response(), $event);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
    }

    public function testTransferFreesProgrammeRegistrationsOfSender(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverParticipant = $participantRepository->getParticipantFromUser($giver);
        $paymentService->confirmPayment($paymentService->createAndPersistNewEventPayment($giverParticipant));

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $programme = $this->createProgramme($app, $event);
        $assignment = $this->getService($app, ProgrammeService::class)->adminAssign($programme, $giverParticipant);
        self::assertNull($assignment->error);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));

        $this->captureSentMessageRecipients($app);

        $controller = $this->getService($app, AdminPaymentController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody([
            'emailFrom' => $giver->email,
            'emailTo' => $recipient->email,
        ]);

        $controller->transferPayment($request, new Response(), $event);

        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
        self::assertSame([], $registrationRepository->findActiveForParticipant($giverParticipant));
        $recipientParticipant = $participantRepository->getParticipantFromUser($recipient);
        self::assertSame([], $registrationRepository->findActiveForParticipant($recipientParticipant));
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event): Programme
    {
        $programme = new Programme();
        $programme->name = 'transfer programme';
        $programme->description = 'description';
        $programme->place = 'main field';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-03 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-03 12:00');
        $programme->capacity = 10;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'transfer section');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $istRepository = $this->getService($app, IstRepository::class);

        $user = $userService->registerEmailUser('admin-transfer-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Admin';
        $ist->lastName = 'Transfer';
        $ist->nickname = 'AT';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $user->email;
        $ist->gender = 'man';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function routedRequest(App $app, Event $event): ServerRequestInterface
    {
        $path = '/v2/event/' . $event->slug . '/admin/payments/transferPayment';

        return $this->createRequest($path, 'POST')
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(
                RouteContext::ROUTING_RESULTS,
                $app->getRouteResolver()->computeRoutingResults($path, 'POST'),
            )
            ->withAttribute('event', $event);
    }
}
