<?php

declare(strict_types=1);

namespace kissj\Programme\Place;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int        $id
 * @property string     $name
 * @property float|null $lat
 * @property float|null $lng
 * @property Event      $event m:hasOne
 */
class ProgrammePlace extends EntityDatetime
{
    public function hasCoordinates(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function getCoordinatesInput(): string
    {
        $lat = $this->lat;
        $lng = $this->lng;
        if ($lat === null || $lng === null) {
            return '';
        }

        return (new Coordinates($lat, $lng))->format();
    }
}
