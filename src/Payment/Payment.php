<?php

namespace kissj\Payment;

use kissj\User\User;
use LeanMapper\Entity;

/**
 * @property int    $id
 * @property string $event
 * @property string $variableSymbol
 * @property string $status
 * @property User   $user m:hasOne
 */
class Payment extends Entity {

}