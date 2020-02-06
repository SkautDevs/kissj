<?php
/**
 * Created by PhpStorm.
 * User: Martin Pecka
 * Date: 8.5.2018
 * Time: 22:05
 */

namespace kissj\PaymentImport;


class FioPaymentImporter implements AutomaticPaymentImporter {

    public function getName(): string {
        return "Fio banka";
    }

    /**
     * @return array of kissj\PaymentImport\Payment
     */
    public function getPayments(): array {
        // TODO: Implement getPayments() method.
        return [];
    }
}
