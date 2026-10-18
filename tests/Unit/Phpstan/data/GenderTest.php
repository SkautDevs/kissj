<?php

declare(strict_types=1);

namespace Tests\Unit\Phpstan\data;

use kissj\Participant\Gender;
use kissj\Payment\PaymentStatus;

class GenderTest
{
    public function ownEnum(): string
    {
        return Gender::Man->value;
    }

    public function foreignEnum(): string
    {
        return PaymentStatus::Paid->value;
    }
}
