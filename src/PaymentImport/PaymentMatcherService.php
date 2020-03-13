<?php

namespace kissj\PaymentImport;

use kissj\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\PaymentImport;

class PaymentMatcherService {

    /** @var PaymentService */
    private $paymentService;

    /** @var PaymentRepository */
    private $paymentRepository;

    public function __construct(PaymentService $paymentService, PaymentRepository $paymentRepository) {

    }

    public function match(array $importedPayments) {
        $toBePaid = $this->paymentRepository->findBy(['status' => 'waiting']);

        $getVs = function ($payment) {
            return $payment->variableSymbol;
        };

        $toBePaidByVs = array_combine(array_map($getVs, $toBePaid), $toBePaid);
        $importedByVs = array_combine(array_map($getVs, $importedPayments), $importedPayments);

        $commonPayments = array_intersect_key($toBePaidByVs, $importedByVs);
        $wrongPayments = array_diff_key($importedByVs, $toBePaidByVs);

        $processedPayments = array();
        $paymentErrors = array();
        foreach ($commonPayments as $vs => $payment) {
            $importedPayment = $importedByVs[$vs];

            if ($payment->price != $importedPayment->amount) {
                $paymentErrors[] = new WrongAmountError($importedPayment, $payment);
            } else if ($importedPayment->currency != "Kč") {
                $paymentErrors[] = new WrongCurrencyError($importedPayment, $payment);
            } else {
                $this->paymentService->setPaymentPaid($payment);
                $processedPayments[] = $importedPayment;
            }
        }

        foreach ($wrongPayments as $wrongPayment) {
            $paymentErrors[] = new UnknownVariableSymbolError($wrongPayment);
        }

        return array($processedPayments, $paymentErrors);
    }
}


abstract class PaymentMatchingError {

    public $importedPayment;
    /** @var PaymentImport\Payment */
    public $repoPayment;

    /** @var Payment\Payment */

    public function __construct($importedPayment, $repoPayment = null) {
        $this->importedPayment = $importedPayment;
        $this->repoPayment = $repoPayment;
    }

    public abstract function getErrorString();
}


class WrongAmountError extends PaymentMatchingError {
    public function getErrorString() {
        return sprintf("Špatná částka. Má být: %s, je: %s.", $this->repoPayment->price, $this->importedPayment->amount);
    }
}

class WrongCurrencyError extends PaymentMatchingError {
    public function getErrorString() {
        return sprintf("Špatná měna. Má být: Kč, je: %s.", $this->importedPayment->currency);
    }
}

class UnknownVariableSymbolError extends PaymentMatchingError {
    public function getErrorString() {
        return sprintf("Neznámý variabilní symbol: %s.", $this->importedPayment->variableSymbol);
    }
}
