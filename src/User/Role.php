<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * @property int    $id
 * @property string $name
 * @property string $event
 * @property string $status
 * @property User   $user m:hasOne
 */
class Role extends Entity {

}