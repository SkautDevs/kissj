<?php

declare(strict_types=1);

namespace kissj\Payment;

enum PaymentMessageSeverity: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'error';
}
