<?php

namespace kissj\BankPayment;

use kissj\Event\Event;

interface IBankPaymentService
{
    public function setBreakpoint(\DateTimeImmutable $dateTime, Event $event): bool;

    public function getAndSafeFreshPaymentsFromBank(Event $event): int;
}
