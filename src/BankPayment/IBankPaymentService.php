<?php

namespace kissj\BankPayment;

use kissj\Event\Event;

interface IBankPaymentService
{
    public function getAndSafeFreshPaymentsFromBank(Event $event): int;
}
