<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use kissj\Event\Event;

class TatraBankPaymentService implements IBankPaymentService
{
    public function getAndSafeFreshPaymentsFromBank(Event $event): int
    {
        // TODO: Implement getAndSafeFreshPaymentsFromBank() method.
        // try https://github.com/pavolbiely/tatrabanka-api
        throw new \RuntimeException('Not implemented yet');
    }
}
