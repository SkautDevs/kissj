<?php

declare(strict_types=1);

namespace Tests\Unit\Participant\Admin;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\Twig;
use Tests\AppTestCase;

class PaymentTransferServiceTest extends AppTestCase
{
    public function testReturnsFalseForDifferentRoles(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'guest', UserStatus::Approved);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseWhenSenderNotPaid(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Open);
        $to = $this->createParticipant($app, 'ist', UserStatus::Open);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseWhenSenderIsCancelled(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Cancelled);
        $to = $this->createParticipant($app, 'ist', UserStatus::Open);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseWhenRecipientAlreadyPaid(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ist', UserStatus::Paid);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseWhenRecipientNotYetApproved(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ist', UserStatus::Open);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.recipientNotApproved',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testReturnsFalseWhenRecipientOnlyClosed(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ist', UserStatus::Closed);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.recipientNotApproved',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testReturnsFalseForNullParticipants(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        self::assertFalse($service->isPaymentTransferPossible(null, null, $this->fakeFlash()));
    }

    public function testReturnsTrueForValidSameRolePaidToUnpaidPair(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsTrueForValidOrganizingTeamPaidToUnpaidPair(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ot', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ot', UserStatus::Approved);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseAndWarnsWhenParticipantIsPatrolLeader(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'pl', UserStatus::Paid);
        $to = $this->createParticipant($app, 'pl', UserStatus::Approved);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.patrolLeaderNotSupported',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testReturnsFalseAndWarnsWhenTroopLeaderRecipientHasParticipants(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        $event = $this->getService($app, EventRepository::class)->get(1);

        $from = $this->createParticipant($app, 'tl', UserStatus::Paid);

        $recipientUser = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $recipientLeader = $userService->createParticipantSetRole($recipientUser, 'tl');
        $recipientUser->status = UserStatus::Approved;
        $userRepository->persist($recipientUser);

        $memberUser = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $memberParticipant = $userService->createParticipantSetRole($memberUser, 'tp');
        $member = $troopParticipantRepository->get($memberParticipant->id);
        $member->troopLeader = $troopLeaderRepository->get($recipientLeader->id);
        $troopParticipantRepository->persist($member);

        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipientUser->id));

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.troopLeaderHasParticipants',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testTransferPaymentMovesMoneyAndStatusesToRecipient(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);
        $recipientWaitingPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($recipient),
        );

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        $from->scarf = 'giver-scarf';
        $to->scarf = 'recipient-scarf';
        $participantRepository->persist($from);
        $participantRepository->persist($to);

        $giverPayDate = DateTimeUtils::getDateTime('2026-01-10');
        $recipientPayDate = DateTimeUtils::getDateTime('2026-02-20');
        $from->registrationPayDate = $giverPayDate;
        $to->registrationPayDate = $recipientPayDate;
        $participantRepository->persist($from);
        $participantRepository->persist($to);

        $service->transferPayment($from, $to);

        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);

        $transferredPayment = $paymentRepository->get($giverPayment->id);
        self::assertInstanceOf(Payment::class, $transferredPayment);
        self::assertSame($to->id, $transferredPayment->participant->id);

        $cancelledPayment = $paymentRepository->get($recipientWaitingPayment->id);
        self::assertInstanceOf(Payment::class, $cancelledPayment);
        self::assertSame(PaymentStatus::Canceled, $cancelledPayment->status);

        $refetchedFrom = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $refetchedTo = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        self::assertSame('giver-scarf', $refetchedTo->scarf);
        self::assertSame($recipientPayDate->format('Y-m-d'), $refetchedFrom->registrationPayDate?->format('Y-m-d'));
        self::assertSame($giverPayDate->format('Y-m-d'), $refetchedTo->registrationPayDate?->format('Y-m-d'));
    }

    public function testTransferPaymentCancelsRecipientsWaitingPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);
        $recipientWaitingPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($recipient),
        );
        // recipient's payment is intentionally left unconfirmed, so it stays PaymentStatus::Waiting

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        $service->transferPayment($from, $to);

        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);

        $cancelledPayment = $paymentRepository->get($recipientWaitingPayment->id);
        self::assertInstanceOf(Payment::class, $cancelledPayment);
        self::assertSame(PaymentStatus::Canceled, $cancelledPayment->status);

        $transferredPayment = $paymentRepository->get($giverPayment->id);
        self::assertInstanceOf(Payment::class, $transferredPayment);
        self::assertSame($to->id, $transferredPayment->participant->id);
    }

    public function testTransferPaymentSucceedsWhenPaidPaymentIsNotFirstRow(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverParticipant = $participantRepository->getParticipantFromUser($giver);
        // first payment is cancelled (e.g. admin price change), second one is the real paid one
        $cancelledPayment = $paymentService->createAndPersistNewEventPayment($giverParticipant);
        $paymentService->cancelPayment($cancelledPayment);
        $paidPayment = $paymentService->createAndPersistNewEventPayment($giverParticipant);
        $paymentService->confirmPayment($paidPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        $service->transferPayment($from, $to);

        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);

        $transferredPayment = $paymentRepository->get($paidPayment->id);
        self::assertInstanceOf(Payment::class, $transferredPayment);
        self::assertSame(PaymentStatus::Paid, $transferredPayment->status);
        self::assertSame($to->id, $transferredPayment->participant->id);
    }

    public function testTransferAbortsWhenGiverAlreadyOpen(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        // giver slipped out of Paid before the transfer executes (e.g. concurrent action)
        $giver->status = UserStatus::Open;
        $userRepository->persist($giver);

        try {
            $service->transferPayment($from, $to);
            self::fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException) {
            // expected
        }

        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);
        $payment = $paymentRepository->get($giverPayment->id);
        self::assertInstanceOf(Payment::class, $payment);
        self::assertSame($from->id, $payment->participant->id);
    }

    public function testTransferAbortsAndCompensatesWhenRecipientAlreadyPaid(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
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

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        // recipient becomes Paid before the transfer executes (e.g. a racing transfer)
        $recipient->status = UserStatus::Paid;
        $userRepository->persist($recipient);

        try {
            $service->transferPayment($from, $to);
            self::fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException) {
            // expected
        }

        // compensation must restore the giver to Paid
        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
    }

    public function testTransferPaymentReassignsTroopParticipantsToNewLeader(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);

        $giverLeader = $this->createTroopLeader($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giverLeader),
        );
        $paymentService->confirmPayment($giverPayment);

        $giverLeaderParticipant = $troopLeaderRepository->getFromUser($userRepository->get($giverLeader->id));

        $member1 = $this->createTroopParticipantTiedTo($app, $event, $giverLeaderParticipant);
        $member2 = $this->createTroopParticipantTiedTo($app, $event, $giverLeaderParticipant);

        $recipientLeader = $this->createTroopLeader($app, $event);
        $recipientLeader->status = UserStatus::Approved;
        $userRepository->persist($recipientLeader);

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giverLeader->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipientLeader->id));

        $service->transferPayment($from, $to);

        self::assertSame(UserStatus::Open, $userRepository->get($giverLeader->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipientLeader->id)->status);

        $refetchedMember1 = $troopParticipantRepository->get($member1->id);
        self::assertNotNull($refetchedMember1->troopLeader);
        self::assertSame($to->id, $refetchedMember1->troopLeader->id);

        $refetchedMember2 = $troopParticipantRepository->get($member2->id);
        self::assertNotNull($refetchedMember2->troopLeader);
        self::assertSame($to->id, $refetchedMember2->troopLeader->id);
    }

    public function testTransferPaymentThrowsWhenRecipientTroopLeaderHasParticipants(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);

        $giverLeader = $this->createTroopLeader($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giverLeader),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipientLeader = $this->createTroopLeader($app, $event);
        $recipientLeader->status = UserStatus::Approved;
        $userRepository->persist($recipientLeader);
        $recipientLeaderParticipant = $troopLeaderRepository->getFromUser($userRepository->get($recipientLeader->id));
        $this->createTroopParticipantTiedTo($app, $event, $recipientLeaderParticipant);

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giverLeader->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipientLeader->id));

        try {
            $service->transferPayment($from, $to);
            self::fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException) {
            // expected
        }

        // the in-method guard fires after the atomic status claims; the surrounding transaction
        // must roll those claims back so neither participant is left half-transferred
        self::assertSame(UserStatus::Paid, $userRepository->get($giverLeader->id)->status);
        self::assertSame(UserStatus::Approved, $userRepository->get($recipientLeader->id)->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $istRepository = $this->getService($app, IstRepository::class);

        $user = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Transfer';
        $ist->lastName = 'Tester';
        $ist->nickname = 'TT';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $user->email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopLeader(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);

        $user = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'tl');

        $troopLeader = $troopLeaderRepository->get($participant->id);
        $troopLeader->patrolName = 'Test Troop';
        $troopLeader->firstName = 'Transfer';
        $troopLeader->lastName = 'Leader';
        $troopLeader->nickname = 'TL';
        $troopLeader->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $troopLeader->email = $user->email;
        $troopLeader->gender = 'male';
        $troopLeader->country = 'CZ';
        $troopLeaderRepository->persist($troopLeader);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopParticipantTiedTo(App $app, Event $event, TroopLeader $troopLeader): TroopParticipant
    {
        $userService = $this->getService($app, UserService::class);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);

        $user = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'tp');

        $troopParticipant = $troopParticipantRepository->get($participant->id);
        $troopParticipant->troopLeader = $troopLeader;
        $troopParticipantRepository->persist($troopParticipant);

        return $troopParticipant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function initializeMailerSettings(App $app, Event $event): void
    {
        $mailerSettings = $this->getService($app, MailerSettings::class);
        $mailerSettings->setEvent($event);
        $mailerSettings->setFullUrlLink('http://test.example.com/v2/event/' . $event->slug);

        $view = $this->getService($app, Twig::class);
        $view->getEnvironment()->addGlobal('event', $event);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createParticipant(App $app, string $role, UserStatus $status): Participant
    {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->get(1);
        $user = $userService->registerEmailUser('transfer-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, $role);

        $user->status = $status;
        $userRepository->persist($user);

        return $participantRepository->getParticipantFromUser($user);
    }

    private function capturingFlash(): FlashMessagesInterface
    {
        return new class () implements FlashMessagesInterface {
            /**
             * @var list<string>
             */
            private array $warnings = [];

            public function info(string $message, array $params = []): void
            {
            }

            public function success(string $message, array $params = []): void
            {
            }

            public function warning(string $message, array $params = []): void
            {
                $this->warnings[] = $message;
            }

            public function error(string $message, array $params = []): void
            {
            }

            /**
             * @return list<array{type: string, message: string}>
             */
            public function dumpMessagesIntoArray(): array
            {
                return array_map(
                    static fn (string $message): array => ['type' => 'warning', 'message' => $message],
                    $this->warnings,
                );
            }
        };
    }

    private function fakeFlash(): FlashMessagesInterface
    {
        return new class () implements FlashMessagesInterface {
            public function info(string $message, array $params = []): void
            {
            }

            public function success(string $message, array $params = []): void
            {
            }

            public function warning(string $message, array $params = []): void
            {
            }

            public function error(string $message, array $params = []): void
            {
            }

            public function dumpMessagesIntoArray(): array
            {
                return [];
            }
        };
    }
}
