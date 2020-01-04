<?php

namespace kissj\Payment;

use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;


/**
 * @property int         $id
 * @property string      $variableSymbol
 * @property string      $price
 * @property string      $currency
 * @property string      $status
 * @property string      $purpose
 * @property string      $accountNumber
 * @property string      $note
 * @property Participant $participant m:hasOne
 */
class Payment extends EntityDatetime {
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELED = 'canceled';
}

/**
 * TODO do not forget add note and rename conventions
 */
