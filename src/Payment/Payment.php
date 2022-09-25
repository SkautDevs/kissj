<?php

declare(strict_types=1);

namespace kissj\Payment;

use DateTimeInterface;
use kissj\Application\StringUtils;
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
            . 'MSG:' . StringUtils::stripDiacritic($this->note) . '*'
            . 'X-VS:' . $this->variableSymbol;
    }
}

/**
 * TODO do not forget add note and rename conventions into new DB
 */
