<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\FinancesStatisticsService;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class FinancesStatisticsServiceTest extends AppTestCase
{
    /** @param App<ContainerInterface> $app */
    private function createPayment(
        App $app,
        Event $event,
        PaymentStatus $status,
        string $price,
        ?string $paidAt = null,
        string $currency = 'Kč',
        string $scarf = Participant::SCARF_NO,
    ): Payment {
        $userService = $this->getService($app, UserService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $email = 'finances-service-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->scarf = $scarf;
        $participantRepository->persist($participant);

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->status = $status;
        $payment->price = $price;
        $payment->currency = $currency;
        $payment->variableSymbol = (string)random_int(1000000000, 9999999999);
        $payment->purpose = 'event fee';
        $payment->accountNumber = '';
        $payment->iban = '';
        $payment->swift = '';
        $payment->constantSymbol = '';
        $payment->note = '';
        $payment->due = DateTimeUtils::getDateTime('+14 days');
        if ($paidAt !== null) {
            $payment->paidAt = DateTimeUtils::getDateTime($paidAt);
        }
        $paymentRepository->persist($payment);

        return $payment;
    }

    public function testMonthlySumsWaitingTotalAndUnknownBucketForPlainEvent(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $event = $this->getObrokTestEvent($app);

        $this->createPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-20');
        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-06-01');
        $this->createPayment($app, $event, PaymentStatus::Paid, '450'); // paidAt stays null
        $this->createPayment($app, $event, PaymentStatus::Waiting, '750');
        $this->createPayment($app, $event, PaymentStatus::Waiting, '600');

        $report = $service->createFinancesReport(
            $event,
            $paymentRepository->getNotCanceledEventPayments($event),
        );

        self::assertSame(
            ['2026-05', '2026-06', FinancesStatisticsService::MONTH_UNKNOWN],
            array_keys($report['months']),
        );
        self::assertSame(['CZK' => 1050.0], $report['months']['2026-05']['sums']);
        self::assertSame(['CZK' => 600.0], $report['months']['2026-06']['sums']);
        self::assertSame(['CZK' => 450.0], $report['months'][FinancesStatisticsService::MONTH_UNKNOWN]['sums']);
        self::assertSame(['CZK' => 2100.0], $report['paidTotal']);
        self::assertSame(['CZK' => 1350.0], $report['waitingTotal']);
        self::assertSame(4, $report['paidCount']);
        self::assertSame(2, $report['waitingCount']);
        self::assertFalse($report['showScarves']);
        self::assertSame([], $report['tiers']);
        self::assertSame(['CZK'], $report['currencies']);
    }

    public function testCurrenciesAreGroupedSeparately(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $event = $this->getObrokTestEvent($app);

        $this->createPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10', 'Kč');
        $this->createPayment($app, $event, PaymentStatus::Paid, '20', '2026-05-11', '€');

        $report = $service->createFinancesReport(
            $event,
            $paymentRepository->getNotCanceledEventPayments($event),
        );

        self::assertSame(['CZK', 'EUR'], $report['currencies']);
        self::assertSame(450.0, $report['months']['2026-05']['sums']['CZK']);
        self::assertSame(20.0, $report['months']['2026-05']['sums']['EUR']);
    }

    public function testKorboScarvesAndTiers(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $this->setEventType($app->getContainer(), 'korbo', 'korbo-finances');
        $event = $eventRepository->findBySlug('korbo-finances');
        self::assertNotNull($event);
        $event->defaultPrice = 450;
        $eventRepository->persist($event);
        $event = $eventRepository->get($event->id);

        $this->createPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-12', 'Kč', Participant::SCARF_YES);
        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-06-01');
        $this->createPayment($app, $event, PaymentStatus::Paid, '750', '2026-06-02', 'Kč', Participant::SCARF_YES);
        $this->createPayment($app, $event, PaymentStatus::Paid, '500', '2026-06-03', 'Kč', Participant::SCARF_YES); // admin-adjusted price - no tier
        $this->createPayment($app, $event, PaymentStatus::Waiting, '600', null, 'Kč', Participant::SCARF_YES);

        $report = $service->createFinancesReport(
            $event,
            $paymentRepository->getNotCanceledEventPayments($event),
        );

        self::assertTrue($report['showScarves']);
        self::assertSame(1, $report['months']['2026-05']['scarves']);
        self::assertSame(2, $report['months']['2026-06']['scarves']);
        self::assertSame(3, $report['paidScarves']);
        self::assertSame(1, $report['waitingScarves']);
        self::assertSame([
            ['price' => 450, 'scarf' => false, 'paidCount' => 1, 'waitingCount' => 0],
            ['price' => 600, 'scarf' => false, 'paidCount' => 1, 'waitingCount' => 0],
            ['price' => 600, 'scarf' => true, 'paidCount' => 1, 'waitingCount' => 1],
            ['price' => 750, 'scarf' => true, 'paidCount' => 1, 'waitingCount' => 0],
            ['price' => null, 'scarf' => null, 'paidCount' => 1, 'waitingCount' => 0],
        ], $report['tiers']);
    }
}
