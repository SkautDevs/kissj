<?php

namespace kissj\User;

use kissj\Orm\EntityDatetime;

/**
 * @property int       $id
 * @property string    $token
 * @property User      $user    m:hasOne
 * @property boolean   $used
 * @property \DateTime $created m:passThru(dateFromString|dateToString)
 */
class LoginToken extends EntityDatetime {

}
