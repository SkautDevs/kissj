<?php

namespace kissj\Payment;

use kissj\Event\Event;
use kissj\User\User;


/**
 * @property int       $id
 * @property string    $variableSymbol
 * @property string    $price
 * @property string    $currency
 * @property string    $status
 * @property string    $purpose
 * @property string    $accountNumber
 * @property \DateTime $generatedDate m:passThru(dateFromString|dateToString)
 * @property User      $user          m:hasOne
 * @property Event     $event         m:hasOne
 */
class Payment {

}
