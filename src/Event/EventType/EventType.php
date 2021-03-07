<?php
declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Participant;

abstract class EventType {
    abstract public function getPrice(Participant $participant): int;
}
