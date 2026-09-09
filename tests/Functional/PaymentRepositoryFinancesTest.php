<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use Tests\AppTestCase;

class PaymentRepositoryFinancesTest extends AppTestCase
{
    public function testReturnsAllStatusesExceptCanceledScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $event = $this->getObrokTestEvent($app);
        $otherEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));

        $paidPayment = $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450');
        $waitingPayment = $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '600');
        $canceledPayment = $this->createFinancesPayment($app, $event, PaymentStatus::Canceled, '450');
        $otherEventPayment = $this->createFinancesPayment($app, $otherEvent, PaymentStatus::Paid, '999');

        $payments = $paymentRepository->getNotCanceledEventPayments($event);

        $paymentIds = array_map(static fn (Payment $payment): int => $payment->id, $payments);
        self::assertContains($paidPayment->id, $paymentIds);
        self::assertContains($waitingPayment->id, $paymentIds);
        self::assertNotContains($canceledPayment->id, $paymentIds);
        self::assertNotContains($otherEventPayment->id, $paymentIds);
    }
}
