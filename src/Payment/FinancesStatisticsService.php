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

    public function createFinancesReport(Event $event): FinancesReport
    {
        $payments = $this->paymentRepository->getNotCanceledEventPayments($event);

        $tiers = $event->getEventType()->getFinanceTiers($event);
        $showScarves = array_filter($tiers, static fn (FinanceTier $tier): bool => $tier instanceof ScarfFinanceTier) !== [];

        $months = [];
        $paidTotal = [];
        $waitingTotal = [];
        $paidCount = 0;
        $waitingCount = 0;
        $paidScarves = 0;
        $waitingScarves = 0;

        $tierStats = array_map(static fn (FinanceTier $tier): FinanceTierStat => new FinanceTierStat($tier), $tiers);
        $otherStat = new FinanceTierStat(new FinanceTierUnknown());

        foreach ($payments as $payment) {
            $currency = Payment::normalizeCurrency($payment->currency) ?? $payment->currency;
            $price = (float)$payment->price;
            $hasScarf = $showScarves && $payment->participant->scarf === Participant::SCARF_YES;
            $isPaid = $payment->status === PaymentStatus::Paid;

            if ($isPaid) {
                $paidCount++;
                $paidTotal[$currency] = ($paidTotal[$currency] ?? 0.0) + $price;

                $monthKey = $payment->paidAt?->format('Y-m') ?? self::MONTH_UNKNOWN;
                $months[$monthKey] ??= new FinanceMonthStat();
                $months[$monthKey]->sums[$currency] = ($months[$monthKey]->sums[$currency] ?? 0.0) + $price;
                if ($hasScarf) {
                    $paidScarves++;
                    $months[$monthKey]->scarves++;
                }
            } else {
                $waitingCount++;
                $waitingTotal[$currency] = ($waitingTotal[$currency] ?? 0.0) + $price;
                if ($hasScarf) {
                    $waitingScarves++;
                }
            }

            if ($tiers !== []) {
                $matchedStat = $otherStat;
                foreach ($tierStats as $stat) {
                    if ($stat->tier->matches($payment)) {
                        $matchedStat = $stat;
                        break;
                    }
                }
                if ($isPaid) {
                    $matchedStat->paidCount++;
                } else {
                    $matchedStat->waitingCount++;
                }
            }
        }

        $unknownMonth = $months[self::MONTH_UNKNOWN] ?? null;
        unset($months[self::MONTH_UNKNOWN]);
        krsort($months);
        if ($unknownMonth !== null) {
            $months[self::MONTH_UNKNOWN] = $unknownMonth;
        }

        $paidCurrencies = array_keys($paidTotal);
        sort($paidCurrencies);
        $waitingCurrencies = array_keys($waitingTotal);
        sort($waitingCurrencies);

        if ($tiers !== [] && ($otherStat->paidCount > 0 || $otherStat->waitingCount > 0)) {
            $tierStats[] = $otherStat;
        }

        return new FinancesReport(
            months: $months,
            paidCurrencies: $paidCurrencies,
            waitingCurrencies: $waitingCurrencies,
            paidTotal: $paidTotal,
            waitingTotal: $waitingTotal,
            paidCount: $paidCount,
            waitingCount: $waitingCount,
            paidScarves: $paidScarves,
            waitingScarves: $waitingScarves,
            showScarves: $showScarves,
            tiers: $tiers !== [] ? $tierStats : [],
        );
    }
}
