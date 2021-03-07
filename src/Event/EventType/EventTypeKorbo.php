<?php
declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Participant;

class EventTypeKorbo extends EventType {
    public function getPrice(Participant $participant): int {
        $price = 500;
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += $participant->user->event->scarfPrice;
        }

        return $price;
    }
}
