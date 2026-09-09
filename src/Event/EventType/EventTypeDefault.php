<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Event\Event;
use kissj\Payment\FinanceTier;

class EventTypeDefault extends EventType
{
    /**
     * @return list<FinanceTier>
     */
    #[\Override]
    public function getFinanceTiers(Event $event): array
    {
        return [
            new FinanceTier($event->defaultPrice),
        ];
    }
}
