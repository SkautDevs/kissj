<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class PaymentRepositoryFinancesTest extends AppTestCase
{
    /** @param App<ContainerInterface> $app */
    private function createPaymentForNewIst(
        App $app,
        Event $event,
        PaymentStatus $status,
        string $price,
    ): Payment {
        $userService = $this->getService($app, UserService::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $email = 'finances-repo-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->status = $status;
        $payment->price = $price;
        $payment->currency = 'Kč';
        $payment->variableSymbol = (string)random_int(1000000000, 9999999999);
        $payment->purpose = 'event fee';
        $payment->accountNumber = '';
        $payment->iban = '';
        $payment->swift = '';
        $payment->constantSymbol = '';
        $payment->note = '';
        $payment->due = DateTimeUtils::getDateTime('+14 days');
        $paymentRepository->persist($payment);

        return $payment;
    }

    public function testReturnsAllStatusesExceptCanceledScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $event = $this->getObrokTestEvent($app);
        $otherEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));

        $paidPayment = $this->createPaymentForNewIst($app, $event, PaymentStatus::Paid, '450');
        $waitingPayment = $this->createPaymentForNewIst($app, $event, PaymentStatus::Waiting, '600');
        $canceledPayment = $this->createPaymentForNewIst($app, $event, PaymentStatus::Canceled, '450');
        $otherEventPayment = $this->createPaymentForNewIst($app, $otherEvent, PaymentStatus::Paid, '999');

        $payments = $paymentRepository->getNotCanceledEventPayments($event);

        $paymentIds = array_map(static fn (Payment $payment): int => $payment->id, $payments);
        self::assertContains($paidPayment->id, $paymentIds);
        self::assertContains($waitingPayment->id, $paymentIds);
        self::assertNotContains($canceledPayment->id, $paymentIds);
        self::assertNotContains($otherEventPayment->id, $paymentIds);
    }
}
