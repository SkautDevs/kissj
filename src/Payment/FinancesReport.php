<?php

declare(strict_types=1);

namespace kissj\Payment;

readonly class FinancesReport
{
    /**
     * @param array<string, FinanceMonthStat> $months
     * @param list<string> $paidCurrencies
     * @param list<string> $waitingCurrencies
     * @param array<string, float> $paidTotal
     * @param array<string, float> $waitingTotal
     * @param list<FinanceTierStat> $tiers
     */
    public function __construct(
        public array $months,
        public array $paidCurrencies,
        public array $waitingCurrencies,
        public array $paidTotal,
        public array $waitingTotal,
        public int $paidCount,
        public int $waitingCount,
        public int $paidScarves,
        public int $waitingScarves,
        public bool $showScarves,
        public array $tiers,
    ) {
    }
}
