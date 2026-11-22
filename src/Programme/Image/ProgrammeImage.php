<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use kissj\Orm\EntityDatetime;
use kissj\Programme\Programme;

/**
 * @property int         $id
 * @property Programme   $programme m:hasOne
 * @property string      $filename
 * @property string|null $thumbFilename
 * @property string      $alt
 * @property int         $sortOrder
 */
class ProgrammeImage extends EntityDatetime
{
}
