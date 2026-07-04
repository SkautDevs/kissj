<?php

declare(strict_types=1);

namespace Tests\Functional;

readonly class CrossEventForeignFixture
{
    public function __construct(
        public int $participantId,
        public int $userId,
        public int $paymentId,
        public int $bankPaymentId,
        public int $programmeId,
    ) {
    }
}
