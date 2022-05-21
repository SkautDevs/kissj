<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

class EventTypeMiquik extends EventType
{
    public function showParticipantInfoInMail(): bool
    {
        return true;
    }
}
