<?php

namespace kissj\Payment;

use http\Exception\RuntimeException;
use kissj\Orm\Repository;

class PaymentRepository extends Repository {
    public function isVariableNumberExisting(string $variableNumber): bool {
        return $this->isExisting(['variable_symbol' => $variableNumber]);
    }

    /**
     * @return Payment[]
     */
    public function getWaitingPaymentsKeydByVariableSymbols(): array {
        $payments = $this->findBy(['status' => Payment::STATUS_WAITING]);

        $finalPayments = [];
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            if (array_key_exists($payment->variableSymbol, $finalPayments)) {
                throw new RuntimeException(
                    'More payments with same variable symbol existing: '.$payment->variableSymbol
                );
            }
            $finalPayments[$payment->variableSymbol] = $payment;
        }

        return $finalPayments;
    }
}
