<?php

declare(strict_types=1);

namespace kissj\Payment;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Application\StringUtils;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int                    $id
 * @property string                 $variableSymbol
 * @property string                 $price
 * @property string                 $currency
 * @property PaymentStatus          $status m:passThru(statusFromString|statusToString)
 * @property string                 $purpose
 * @property string                 $accountNumber
 * @property string                 $iban
 * @property string                 $swift
 * @property string                 $constantSymbol
 * @property DateTimeInterface      $due m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $paidAt m:passThru(dateFromString|dateToString)
 * @property string                 $note
 * @property Participant            $participant m:hasOne
 */
class Payment extends EntityDatetime
{
    public function initDefaults(): void
    {
        parent::initDefaults();
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
        $now = DateTimeUtils::getDateTime();
        $daysDiff = ($now)->diff($this->due)->days;

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
        $swiftPart = '';
        if ($this->swift !== '') {
            $swiftPart = '+' . $this->swift;
        }

        return
            'SPD*1.0*ACC:' . $this->iban . $swiftPart . '*'
            . 'AM:' . $this->price . '*'
            . 'CC:' . $this->mapDbCurrencyToIban($this->currency) . '*'
            . 'MSG:' . StringUtils::stripDiacritic($this->note) . '*'
            . ($this->constantSymbol === '' ? '' : ('X-KS:' . $this->constantSymbol . '*'))
            . 'X-VS:' . $this->variableSymbol;
    }

    private function mapDbCurrencyToIban(string $currency): string
    {
        return self::normalizeCurrency($currency) ?? 'CZK';
    }

    // event.currency holds free-form text ('Kč', '€', 'euro'), banks send ISO codes -
    // both sides of any comparison must go through this table
    public static function normalizeCurrency(?string $currency): ?string
    {
        if ($currency === null) {
            return null;
        }

        $normalized = mb_strtoupper(trim($currency));

        return match (true) {
            $normalized === 'KČ', $normalized === 'CZK' => 'CZK',
            $normalized === '€', $normalized === 'EURO', $normalized === 'EUR' => 'EUR',
            preg_match('/^[A-Z]{3}$/', $normalized) === 1 => $normalized,
            default => null,
        };
    }
}
