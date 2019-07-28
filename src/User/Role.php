<?php

namespace kissj\User;

use kissj\Event\Event;
use LeanMapper\Entity;

/**
 * @property int    $id
 * @property string $name
 * @property Event  $event m:hasOne
 * @property string $status
 * @property User   $user m:hasOne
 */
class Role extends Entity {

}