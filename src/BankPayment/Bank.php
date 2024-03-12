<?php

declare(strict_types=1);

namespace kissj\BankPayment;

readonly class Bank
{
    public function __construct(
        public string $slug,
        public string $code,
        public string $name,
        public string $serviceClass,
    ) {
    }
}
