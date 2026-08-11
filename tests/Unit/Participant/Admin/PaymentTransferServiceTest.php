<?php

declare(strict_types=1);

namespace Tests\Unit\Participant\Admin;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\FlashMessages\NullFlashMessages;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
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
use Tests\AppTestCase;

class PaymentTransferServiceTest extends AppTestCase
{
    public function testRefusesWhenEitherParticipantIsNull(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        self::assertFalse($service->isPaymentTransferPossible(null, null, new NullFlashMessages()));
    }

    public function testRefusesDifferentRoles(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'guest', UserStatus::Approved);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, new NullFlashMessages()));
    }

    public function testRefusesWhenSenderNotPaidOrCancelled(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Open);
        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains('flash.warning.notPaid', array_column($flash->dumpMessagesIntoArray(), 'message'));
    }

    public function testRefusesWhenRecipientAlreadyPaid(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ist', UserStatus::Paid);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains('flash.warning.isPaid', array_column($flash->dumpMessagesIntoArray(), 'message'));
    }

    public function testRefusesCancelledSender(): void
    {
        // sender must be exactly Paid - a cancelled registration has no live claim to transfer
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Cancelled);
        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        self::assertFalse($service->isPaymentTransferPossible($from, $to, new NullFlashMessages()));
    }

    public function testRefusesRecipientThatIsNotApproved(): void
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

    public function testAllowsWhenRolesMatchAndStatusesAreCorrect(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);
        $service = $this->getService($app, PaymentTransferService::class);
        $paymentService = $this->getService($app, PaymentService::class);

        // Paid status alone is not enough since senderHasNoPayment was added - a real
        // paid Payment is what makes this a genuine (non-free) paid registration
        $from = $this->createParticipant($app, 'ist', UserStatus::Open);
        $payment = $paymentService->createAndPersistNewEventPayment($from);
        $paymentService->confirmPayment($payment);
        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, new NullFlashMessages()));
    }

    public function testTransferPaymentMovesPaymentAndFlipsStatuses(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $paymentService = $this->getService($app, PaymentService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Open);
        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        $payment = $paymentService->createAndPersistNewEventPayment($from);
        $paymentService->confirmPayment($payment);
        $paymentId = $payment->id;

        $service->transferPayment(
            $participantRepository->getParticipantById($from->id, $event),
            $participantRepository->getParticipantById($to->id, $event),
        );

        $userFrom = $userRepository->get($from->getUserButNotNull()->id);
        $userTo = $userRepository->get($to->getUserButNotNull()->id);
        self::assertSame(UserStatus::Open, $userFrom->status);
        self::assertSame(UserStatus::Paid, $userTo->status);

        $transferredPayment = $paymentRepository->getById($paymentId, $event);
        self::assertSame($to->id, $transferredPayment->participant->id);
    }

    public function testRefusesSenderWithoutPaidPayment(): void
    {
        // a zero-price registration is marked Paid with no Payment row; without this the transfer
        // is offered and then always throws inside handlePayments
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'guest', UserStatus::Paid); // no payment created
        $to = $this->createParticipant($app, 'guest', UserStatus::Approved);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.senderHasNoPayment',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testAllowsSenderWhosePaidPaymentFollowsACancelledOne(): void
    {
        // an admin price change leaves the sender with [Canceled, Paid] - a key-preserving
        // filter would hide the paid payment and refuse the transfer as "free registration"
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $service = $this->getService($app, PaymentTransferService::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $paymentService = $this->getService($app, PaymentService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $from = $this->createParticipant($app, 'ist', UserStatus::Open);
        $originalPayment = $paymentService->createAndPersistNewEventPayment($from);
        $repricedPayment = $participantService->changePaymentPrice($originalPayment, 300, 'admin discount');
        $paymentService->confirmPayment($repricedPayment);

        $to = $this->createParticipant($app, 'ist', UserStatus::Approved);

        $sender = $participantRepository->getParticipantById($from->id, $event);
        self::assertSame(PaymentStatus::Canceled, $sender->getPayments()[0]->status);
        self::assertSame($repricedPayment->id, $sender->getFirstPaidPayment()?->id);
        self::assertCount(1, $sender->getAllPaidPayment());
        self::assertTrue($service->isPaymentTransferPossible($sender, $to, new NullFlashMessages()));
    }

    public function testRefusesRecipientAlreadyInTroop(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);
        $event = $this->getService($app, EventRepository::class)->get(1);

        $senderLeader = $this->createTroopLeader($app, $event);
        $otherLeader = $this->createTroopLeader($app, $event);
        $from = $this->createTroopParticipantTiedTo($app, $event, $this->troopLeaderOf($app, $senderLeader));
        $to = $this->createTroopParticipantTiedTo($app, $event, $this->troopLeaderOf($app, $otherLeader));
        $this->setStatus($app, $from, UserStatus::Paid);
        $this->setStatus($app, $to, UserStatus::Approved);

        $flash = $this->capturingFlash();
        self::assertFalse($service->isPaymentTransferPossible($from, $to, $flash));
        self::assertContains(
            'flash.warning.recipientHasTroop',
            array_column($flash->dumpMessagesIntoArray(), 'message'),
        );
    }

    public function testAcceptsTroopLessRecipient(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);
        $event = $this->getService($app, EventRepository::class)->get(1);

        $senderLeader = $this->createTroopLeader($app, $event);
        $from = $this->createTroopParticipantTiedTo($app, $event, $this->troopLeaderOf($app, $senderLeader));
        $to = $this->createParticipant($app, 'tp', UserStatus::Approved); // never tied to a troop
        $this->setStatus($app, $from, UserStatus::Paid);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, new NullFlashMessages()));
    }

    public function testTransferCopiesScarfButNotTshirt(): void
    {
        // priced attributes follow the payment, personal ones stay with the person:
        // scarf is charged for by Korbo and Jj inside getPrice, a tshirt size is a body measurement
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);
        $service = $this->getService($app, PaymentTransferService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        [$from, $to] = $this->createPaidAndApprovedIstPair($app, $event);
        $from->scarf = Participant::SCARF_YES;
        $from->setTshirt('men', 'XL');
        $to->scarf = Participant::SCARF_NO;
        $to->setTshirt('women', 'S');
        $participantRepository->persist($from);
        $participantRepository->persist($to);

        $service->transferPayment($from, $to);

        $recipient = $participantRepository->getParticipantById($to->id, $event);
        self::assertSame(Participant::SCARF_YES, $recipient->scarf);
        self::assertSame('S', $recipient->getTshirtSize());
        self::assertSame('women', $recipient->getTshirtShape());
    }

    public function testTransferCancelsRecipientWaitingPaymentAndMailsEachSideOnce(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);
        $service = $this->getService($app, PaymentTransferService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        [$from, $to] = $this->createPaidAndApprovedIstPair($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);

        $service->transferPayment($from, $to);

        $recipientStatuses = array_map(
            static fn (Payment $payment): string => $payment->status->value,
            $participantRepository->getParticipantById($to->id, $event)->getPayments(),
        );
        sort($recipientStatuses);
        self::assertSame([PaymentStatus::Canceled->value, PaymentStatus::Paid->value], $recipientStatuses);

        // one mail per side — the cancelled waiting payment is implied by "your registration is now paid"
        $counts = array_count_values($sentTo->getArrayCopy());
        self::assertSame(1, $counts[$to->getUserButNotNull()->email] ?? 0);
        self::assertSame(1, $counts[$from->getUserButNotNull()->email] ?? 0);
        self::assertCount(2, $sentTo);
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

    /**
     * @param App<ContainerInterface> $app
     */
    private function troopLeaderOf(App $app, User $user): TroopLeader
    {
        return $this->getService($app, TroopLeaderRepository::class)->getFromUser($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setStatus(App $app, Participant $participant, UserStatus $status): void
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $userRepository->persist($user);
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
     * A real Payment row on the sender (not the free 0-price shortcut) is required for
     * transferPayment's handlePayments() to have something to move.
     *
     * @param App<ContainerInterface> $app
     * @return array{0: Participant, 1: Participant}
     */
    private function createPaidAndApprovedIstPair(App $app, Event $event): array
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $liveEvent = $eventRepository->get($event->id);
        $liveEvent->defaultPrice = 500;
        $eventRepository->persist($liveEvent);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $senderUser = $this->createIst($app, $event);
        $sender = $participantRepository->getParticipantFromUser($senderUser);
        $participantService->approveRegistration($sender);
        $payment = $participantRepository->getParticipantFromUser($senderUser)->getPayments()[0];
        $paymentService->confirmPayment($payment);
        $from = $participantRepository->getParticipantFromUser($senderUser);

        $recipientUser = $this->createIst($app, $event);
        $recipient = $participantRepository->getParticipantFromUser($recipientUser);
        $participantService->approveRegistration($recipient);
        $to = $participantRepository->getParticipantFromUser($recipientUser);

        return [$from, $to];
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
}
