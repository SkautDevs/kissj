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
use kissj\Participant\Troop\TroopLeaderRepository;
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
        $to = $this->createParticipant($app, 'guest', UserStatus::Open);

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
        $to = $this->createParticipant($app, 'ist', UserStatus::Open);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsTrueForValidOrganizingTeamPaidToUnpaidPair(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'ot', UserStatus::Paid);
        $to = $this->createParticipant($app, 'ot', UserStatus::Open);

        self::assertTrue($service->isPaymentTransferPossible($from, $to, $this->fakeFlash()));
    }

    public function testReturnsFalseAndWarnsWhenParticipantIsPatrolLeader(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, PaymentTransferService::class);

        $from = $this->createParticipant($app, 'pl', UserStatus::Paid);
        $to = $this->createParticipant($app, 'pl', UserStatus::Open);

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
        $recipientUser->status = UserStatus::Open;
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
        $recipient->status = UserStatus::Open;
        $userRepository->persist($recipient);
        $recipientWaitingPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($recipient),
        );

        $from = $participantRepository->getParticipantFromUser($userRepository->get($giver->id));
        $to = $participantRepository->getParticipantFromUser($userRepository->get($recipient->id));

        $service->transferPayment($from, $to);

        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);

        $transferredPayment = $paymentRepository->get($giverPayment->id);
        self::assertInstanceOf(Payment::class, $transferredPayment);
        self::assertSame($to->id, $transferredPayment->participant->id);

        $cancelledPayment = $paymentRepository->get($recipientWaitingPayment->id);
        self::assertInstanceOf(Payment::class, $cancelledPayment);
        self::assertSame(PaymentStatus::Canceled, $cancelledPayment->status);
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
