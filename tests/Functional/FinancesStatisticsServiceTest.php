<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Payment\FinancesStatisticsService;
use kissj\Payment\PaymentStatus;
use Tests\AppTestCase;

class FinancesStatisticsServiceTest extends AppTestCase
{
    public function testMonthlySumsWaitingTotalAndUnknownBucketForPlainEvent(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-20');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-06-01');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450'); // paidAt stays null
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '750');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '600');
        $this->createFinancesPayment($app, $event, PaymentStatus::Canceled, '999');

        $report = $service->createFinancesReport($event);

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
        self::assertSame(['CZK'], $report['paidCurrencies']);
        self::assertSame(['CZK'], $report['waitingCurrencies']);
    }

    public function testCurrenciesAreGroupedSeparatelyForPaidAndWaiting(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10', 'Kč');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '20', '2026-05-11', '€');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '30', null, '€');

        $report = $service->createFinancesReport($event);

        self::assertSame(['CZK', 'EUR'], $report['paidCurrencies']);
        self::assertSame(['EUR'], $report['waitingCurrencies']);
        self::assertSame(450.0, $report['months']['2026-05']['sums']['CZK']);
        self::assertSame(20.0, $report['months']['2026-05']['sums']['EUR']);
    }

    public function testKorboScarvesAndTiers(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $this->setEventType($app->getContainer(), 'korbo', 'korbo-finances');
        $event = $eventRepository->findBySlug('korbo-finances');
        self::assertNotNull($event);
        $event->defaultPrice = 450;
        $eventRepository->persist($event);
        $event = $eventRepository->get($event->id);

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-12', 'Kč', Participant::SCARF_YES);
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-06-01');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '750', '2026-06-02', 'Kč', Participant::SCARF_YES);
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '500', '2026-06-03', 'Kč', Participant::SCARF_YES); // admin-adjusted price - no tier
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '600', null, 'Kč', Participant::SCARF_YES);

        $report = $service->createFinancesReport($event);

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

    public function testKorboTiersOmitOtherRowWhenNoOffTierPayments(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, FinancesStatisticsService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $this->setEventType($app->getContainer(), 'korbo', 'korbo-finances-no-other');
        $event = $eventRepository->findBySlug('korbo-finances-no-other');
        self::assertNotNull($event);
        $event->defaultPrice = 450;
        $eventRepository->persist($event);
        $event = $eventRepository->get($event->id);

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-12', 'Kč', Participant::SCARF_YES);

        $report = $service->createFinancesReport($event);

        self::assertCount(4, $report['tiers']);
        foreach ($report['tiers'] as $tier) {
            self::assertNotNull($tier['price']);
        }
    }
}
