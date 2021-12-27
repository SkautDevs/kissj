<?php declare(strict_types=1);

namespace kissj\Payment;

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
                throw new \RuntimeException(
                    'More payments with same variable symbol existing: '.$payment->variableSymbol
                );
            }
            $finalPayments[$payment->variableSymbol] = $payment;
        }

        return $finalPayments;
    }

    /**
     * @return Payment[]
     */
    public function getDuePayments(): array
    {
        /** @var Payment[] $waitingPayments */
        $waitingPayments = $this->findBy(['status' => Payment::STATUS_WAITING]);
        
        return array_filter(
            $waitingPayments,
            fn(Payment $payment) => $payment->getElapsedPaymentDays() > $payment->getMaxElapsedPaymentDays()
        );
    }
}
