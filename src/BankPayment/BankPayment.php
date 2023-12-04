<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use DateTimeInterface;
use h4kuna\Fio\Response\Read\Transaction;
use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int                    $id
 * @property Event                  $event m:hasOne
 * @property string|null            $bankId
 * @property DateTimeInterface|null $moveDate m:passThru(dateFromString|dateToString)
 * @property string|null            $price
 * @property string|null            $variableSymbol
 * @property string|null            $accountNumber
 * @property string|null            $constantSymbol
 * @property string|null            $specificSymbol
 * @property string|null            $note
 * @property string|null            $currency
 * @property string|null            $message
 * @property string|null            $nameAccountFrom
 * @property string|null            $comment
 * @property string|null            $status m:enum(self::STATUS_*)
 */
class BankPayment extends EntityDatetime
{
    public const STATUS_FRESH = 'fresh';
    public const STATUS_PAIRED = 'paired';
    public const STATUS_UNKNOWN = 'unknown';
    public const STATUS_UNRELATED = 'unrelated';
    public const STATUS_RETURNED = 'returned';

    /**
     * @param Transaction $t
     * @param Event $event
     * @return $this
     */
    public function mapTransactionInto(Transaction $t, Event $event): self
    {
        $this->event = $event;
        $this->bankId = (string)$t->moveId;
        $this->moveDate = $t->moveDate;
        $this->price = (string)$t->volume;
        $this->variableSymbol = $t->variableSymbol;
        $this->accountNumber = $t->toAccount . '/' . $t->bankCode;
        $this->constantSymbol = $t->constantSymbol;
        $this->specificSymbol = $t->specificSymbol;
        $this->note = $t->note;
        $this->currency = $t->currency;
        $this->message = $t->messageTo;
        $this->nameAccountFrom = $t->nameAccountTo;
        $this->comment = $t->comment;
        $this->status = self::STATUS_FRESH;

        return $this;
    }
}
