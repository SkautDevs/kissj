<?php

declare(strict_types=1);

namespace kissj\Payment;

use kissj\Participant\Participant;

readonly class ScarfFinanceTier extends FinanceTier
{
    public function __construct(
        int $price,
        public bool $scarf,
    ) {
        parent::__construct($price);
    }

    #[\Override]
    public function matches(Payment $payment): bool
    {
        return parent::matches($payment)
            && $this->scarf === ($payment->participant->scarf === Participant::SCARF_YES);
    }
}
