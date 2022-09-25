<?php

declare(strict_types=1);

namespace kissj\Payment;

use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int               $id
 * @property string            $variableSymbol
 * @property string            $price
 * @property string            $currency
 * @property PaymentStatus     $status m:passThru(statusFromString|statusToString)
 * @property string            $purpose
 * @property string            $accountNumber
 * @property string            $iban
 * @property DateTimeInterface $due m:passThru(dateFromString|dateToString)
 * @property string            $note
 * @property Participant       $participant m:hasOne
 */
class Payment extends EntityDatetime
{
    public function initDefaults(): void
    {
        $this->status = PaymentStatus::Waiting;
    }

    public function statusFromString(string $status): PaymentStatus
    {
        return PaymentStatus::from($status);
    }

    public function statusToString(PaymentStatus $status): string
    {
        return $status->value;
    }

    public function getRemainingDays(): int
    {
        $now = new \DateTimeImmutable();
        $daysDiff = ($now)->diff($this->due, false)->days;

        if ($daysDiff === false) {
            throw new \RuntimeException('DateTime diff returns false');
        }

        if ($now > $this->due) {
            return -$daysDiff;
        }

        return $daysDiff;
    }

    public function isPaymentOverdue(): bool
    {
        return $this->getRemainingDays() < 0;
    }

    public function getQrPaymentString(): string
    {
        return
            'SPD*1.0*ACC:' . $this->iban . '*'
            . 'AM:' . $this->price . '*'
            . 'CC:' . 'CZK' . '*' // TODO change into $payment->currency
            . 'DT:' . $this->due->format('Ymd') . '*'
            . 'MSG:' . $this->getNoteWithoutDiacritic() . '*'
            . 'X-VS:' . $this->variableSymbol;
    }

    // TODO move into string utils
    public function getNoteWithoutDiacritic(): string
    {
        $diacritic = [
            'ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ú', 'ů', 'ť', 'ď', 'ó', 'ä', 'ë', 'ü',
            'Ě', 'Š', 'Č', 'Ř', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ů', 'Ť', 'Ď', 'Ó', 'Ä', 'Ë', 'Ü',
        ];
        $without = [
            'e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'u', 'u', 't', 'd', 'ó', 'a', 'e', 'u',
            'E', 'S', 'C', 'R', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U', 'T', 'D', 'Ó', 'A', 'E', 'U',
        ];

        return str_replace($diacritic, $without, $this->note);
    }
}

/**
 * TODO do not forget add note and rename conventions into new DB
 */
