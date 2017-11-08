<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int $id
 * @property string $token
 * @property User $user m:hasOne
 * @property boolean $used
 * @property \DateTime $created
 */
class LoginToken extends Entity {

}