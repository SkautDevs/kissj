<?php

namespace kissj\Payment;

use kissj\User\Role;
use LeanMapper\Entity;

/**
 * @property int    $id
 * @property string $event
 * @property string $variableSymbol
 * @property string $price
 * @property string $currency
 * @property string $status
 * @property string $purpose
 * @property Role   $role m:hasOne
 */
class Payment extends Entity {

}