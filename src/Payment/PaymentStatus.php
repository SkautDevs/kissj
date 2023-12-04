<?php

declare(strict_types=1);

namespace kissj\Payment;

enum PaymentStatus: string
{
    case Waiting = 'waiting';
    case Paid = 'paid';
    case Canceled = 'canceled';
}
