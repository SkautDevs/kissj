<?php

namespace kissj\Payment;

use kissj\Orm\Repository;
use kissj\Participant\FreeParticipant\FreeParticipant;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;

class PaymentRepository extends Repository {
    // TODO move into more aproppiet place (event db/event class)
    public function createAndPersistNewPayment(Participant $participant, int $price): Payment {
        // seven places long prefixed random "variable number"
        // actually used in note field only, actual variable number must be fixed (in template)
        do {
            $variableNumber = '2020'.str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
        } while ($this->isVariableNumberExisting($variableNumber));

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->variableSymbol = $variableNumber;
        $payment->price = (string)$price;
        $payment->currency = '€';
        $payment->status = Payment::STATUS_WAITING;
        $payment->purpose = 'event fee';
        $payment->accountNumber = 'SK98 1100 0000 0026 6008 0180';
        if ($participant instanceof Ist) {
            $payment->note = 'AQUASTAFF '.$payment->variableSymbol.' '.$participant->getFullName();
        } elseif ($participant instanceof FreeParticipant) {
            $payment->note = 'AQUA SOLO '.$payment->variableSymbol.' '.$participant->getFullName();
        } else {
            $payment->note = 'AQUA 2020 '.$payment->variableSymbol.' '.$participant->getFullName();
        }

        $this->persist($payment);

        return $payment;
    }

    private function isVariableNumberExisting(string $variableNumber): bool {
        return $this->isExisting(['variable_symbol' => $variableNumber]);
    }
}
