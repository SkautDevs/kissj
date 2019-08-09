<?php

namespace kissj\User;

use LeanMapper\Entity;


/**
 * Class User
 * @property int    $id
 * @property string $email
 * @property string $name
 * @property Event  $event m:hasOne
 * @property string $status
 * @property User   $user  m:hasOne
 */
class User extends Entity {

}
