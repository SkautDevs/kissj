<?php

declare(strict_types=1);

namespace Tests\Functional;

enum CrossEventIdKind
{
    case Participant;
    case Payment;
    case BankPayment;
    case Programme;
    case ProgrammeSection;
}
