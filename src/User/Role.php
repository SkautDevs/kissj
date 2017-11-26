<?php

namespace kissj\User;

use kissj\User\User;
use LeanMapper\Entity;

/**
 * @property int       $id
 * @property string    $name
 * @property User      $user m:hasOne
 */
class Role extends Entity {
	
}