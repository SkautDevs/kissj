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
 * @property Participant $participant   m:hasOne
 */
class Payment extends EntityDatetime {

}
