<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int         $id
 * @property string      $name
 * @property string|null $subtitle
 * @property bool        $isExclusive
 * @property int         $sortOrder
 * @property Event       $event m:hasOne
 */
class ProgrammeSection extends EntityDatetime
{
    public function getLabel(): string
    {
        if ($this->subtitle === null) {
            return $this->name;
        }

        return $this->name . ' (' . $this->subtitle . ')';
    }
}
