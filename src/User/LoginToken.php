<?php

namespace kissj\User;

use kissj\Orm\EntityDatetime;

/**
 * @property int       $id
 * @property string    $token
 * @property User      $user    m:hasOne
 * @property boolean   $used
 */
class LoginToken extends EntityDatetime {

}
