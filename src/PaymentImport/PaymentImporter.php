<?php

namespace kissj\PaymentImport;

interface PaymentImporter {
    public function getName(): string;

    /**
     * @return Payment[]
     */
    public function getPayments(): array;
}
