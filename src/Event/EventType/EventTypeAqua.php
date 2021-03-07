<?php
declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeAqua extends EventType {
    /**
     * Participants pays 150€ till 15/3/20, 160€ from 16/3/20, staff 50€
     * discount 40€ for self-eating participant (not for ISTs)
     *
     * @param Participant $participant
     * @return int
     */
    public function getPrice(Participant $participant): int {
        if ($participant instanceof PatrolLeader) {
            $todayPrice = $this->getFullPriceForToday();
            $patrolPriceSum = 0;
            $fullPatrol = array_merge([$participant], $participant->patrolParticipants);
            /** @var Participant $patrolParticipant */
            foreach ($fullPatrol as $patrolParticipant) {
                $patrolPriceSum += $todayPrice;
                if ($patrolParticipant->foodPreferences === Participant::FOOD_OTHER) {
                    $patrolPriceSum -= 40;
                }
            }

            return $patrolPriceSum;
        }

        if ($participant instanceof Ist) {
            return 60;
        }

        throw new \RuntimeException('Generating price for unknown role - participant ID: '.$participant->id);
    }
    
    private function getFullPriceForToday(): int {
        $lastDiscountDay = new \DateTime('2020-03-20');

        if (new \DateTime('now') <= $lastDiscountDay) {
            return 150;
        }

        return 160;
    }
}
