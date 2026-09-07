<?php

declare(strict_types=1);

namespace kissj\Payment;

use kissj\Event\Event;
use kissj\Participant\Participant;

readonly class FinancesStatisticsService
{
    public const string MONTH_UNKNOWN = 'unknown';

    public function __construct(
        private PaymentRepository $paymentRepository,
    ) {
    }

    /**
     * @return array{
     *     months: array<string, array{sums: array<string, float>, scarves: int}>,
     *     paidCurrencies: list<string>,
     *     waitingCurrencies: list<string>,
     *     paidTotal: array<string, float>,
     *     waitingTotal: array<string, float>,
     *     paidCount: int,
     *     waitingCount: int,
     *     paidScarves: int,
     *     waitingScarves: int,
     *     showScarves: bool,
     *     tiers: list<array{price: int|null, scarf: bool|null, paidCount: int, waitingCount: int}>,
     * }
     */
    public function createFinancesReport(Event $event): array
    {
        $payments = $this->paymentRepository->getNotCanceledEventPayments($event);

        $tiers = $event->getEventType()->getFinanceTiers($event);
        $showScarves = $tiers !== [];

        $months = [];
        $paidTotal = [];
        $waitingTotal = [];
        $paidCount = 0;
        $waitingCount = 0;
        $paidScarves = 0;
        $waitingScarves = 0;

        /** @var list<array{price: int|null, scarf: bool|null, paidCount: int, waitingCount: int}> $tierStats */
        $tierStats = [];
        foreach ($tiers as $tier) {
            $tierStats[] = ['price' => $tier['price'], 'scarf' => $tier['scarf'], 'paidCount' => 0, 'waitingCount' => 0];
        }
        /** @var array{price: int|null, scarf: bool|null, paidCount: int, waitingCount: int} $otherTier */
        $otherTier = ['price' => null, 'scarf' => null, 'paidCount' => 0, 'waitingCount' => 0];

        foreach ($payments as $payment) {
            $currency = Payment::normalizeCurrency($payment->currency) ?? $payment->currency;
            $price = (float)$payment->price;
            $hasScarf = $showScarves && $payment->participant->scarf === Participant::SCARF_YES;
            $isPaid = $payment->status === PaymentStatus::Paid;

            if ($isPaid) {
                $paidCount++;
                $paidTotal[$currency] = ($paidTotal[$currency] ?? 0.0) + $price;

                $monthKey = $payment->paidAt?->format('Y-m') ?? self::MONTH_UNKNOWN;
                $months[$monthKey] ??= ['sums' => [], 'scarves' => 0];
                $months[$monthKey]['sums'][$currency] = ($months[$monthKey]['sums'][$currency] ?? 0.0) + $price;
                if ($hasScarf) {
                    $paidScarves++;
                    $months[$monthKey]['scarves']++;
                }
            } else {
                $waitingCount++;
                $waitingTotal[$currency] = ($waitingTotal[$currency] ?? 0.0) + $price;
                if ($hasScarf) {
                    $waitingScarves++;
                }
            }

            if ($showScarves) {
                $countKey = $isPaid ? 'paidCount' : 'waitingCount';
                $matched = false;
                foreach ($tierStats as $index => $tierStat) {
                    if ((float)$tierStat['price'] === $price && $tierStat['scarf'] === $hasScarf) {
                        $tierStats[$index][$countKey]++;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $otherTier[$countKey]++;
                }
            }
        }

        // 'unknown' would sort before numeric months, so it is pulled out and re-appended last
        $unknownMonth = $months[self::MONTH_UNKNOWN] ?? null;
        unset($months[self::MONTH_UNKNOWN]);
        ksort($months);
        if ($unknownMonth !== null) {
            $months[self::MONTH_UNKNOWN] = $unknownMonth;
        }

        $paidCurrencies = array_keys($paidTotal);
        sort($paidCurrencies);
        $waitingCurrencies = array_keys($waitingTotal);
        sort($waitingCurrencies);

        if ($showScarves && ($otherTier['paidCount'] > 0 || $otherTier['waitingCount'] > 0)) {
            $tierStats[] = $otherTier;
        }

        return [
            'months' => $months,
            'paidCurrencies' => $paidCurrencies,
            'waitingCurrencies' => $waitingCurrencies,
            'paidTotal' => $paidTotal,
            'waitingTotal' => $waitingTotal,
            'paidCount' => $paidCount,
            'waitingCount' => $waitingCount,
            'paidScarves' => $paidScarves,
            'waitingScarves' => $waitingScarves,
            'showScarves' => $showScarves,
            'tiers' => $showScarves ? $tierStats : [],
        ];
    }
}
