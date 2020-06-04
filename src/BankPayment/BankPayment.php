<?php

namespace kissj\BankPayment;

use h4kuna\Fio\Response\Read\Transaction;
use kissj\Orm\EntityDatetime;

/**
 * @property int    $id
 * @property string $bankId
 * @property string $moveDate
 * @property string $price
 * @property string $variableSymbol
 * @property string $accountNumber
 * @property string $constantSymbol
 * @property string $specificSymbol
 * @property string $note
 * @property string $currency
 * @property string $message
 * @property string $advancedInformation
 * @property string $comment
 * @property string $status m:enum(self::STATUS_*)
 */
class BankPayment extends EntityDatetime {
    public const STATUS_FRESH = 'fresh';
    public const STATUS_PAIRED = 'paired';
    public const STATUS_UNKNOWN = 'unknown';
    public const STATUS_UNRELATED = 'unrelated';
    public const STATUS_RETURNED = 'returned';

    public function mapTransactionInto(Transaction $t): self {
        $this->bankId = (string)$t->moveId;
        $this->moveDate = $t->moveDate;
        $this->price = $t->volume;
        $this->variableSymbol = $t->variableSymbol;
        $this->accountNumber = $t->toAccount.'/'.$t->bankCode;
        $this->constantSymbol = $t->constantSymbol;
        $this->specificSymbol = $t->specificSymbol;
        $this->note = $t->note;
        $this->currency = $t->currency;
        $this->message = $t->messageTo;
        $this->advancedInformation = $t->advancedInformation;
        $this->comment = $t->comment;
        $this->status = self::STATUS_FRESH;

        return $this;
    }
}
