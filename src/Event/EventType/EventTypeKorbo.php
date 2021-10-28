<?php
declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Participant;

class EventTypeKorbo extends EventType {
    private const SCARF_PRICE = 90;
    
    public function getPrice(Participant $participant): int {
        $price = 500;
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += self::SCARF_PRICE;
        }

        return $price;
    }
}
