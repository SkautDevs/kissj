<?php

namespace kissj\BankPayment;

interface IBankPaymentService {
    public function setBreakpoint(\DateTimeImmutable $dateTime): bool;

    public function getAndSafeFreshPaymentsFromBank(): int;
}
