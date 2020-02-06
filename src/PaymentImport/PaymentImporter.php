<?php

namespace kissj\PaymentImport;

interface PaymentImporter {
    public function getName(): string;

    /**
     * @return array of kissj\PaymentImport\Payment
     */
    public function getPayments(): array;
}
