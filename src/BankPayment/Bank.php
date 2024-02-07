<?php

declare(strict_types=1);

namespace kissj\BankPayment;

class Bank
{
    public function __construct(
        public readonly string $slug,
        public readonly string $code,
        public readonly string $name,
        public readonly string $serviceClass,
    ) {
    }
}
