<?php

declare(strict_types=1);

namespace kissj\Deal;

use kissj\Orm\Repository;
use kissj\Participant\Participant;

class DealRepository extends Repository
{
    /**
     * @return Deal[]
     */
    public function obtainAllDealsForParticipant(Participant $participant): array
    {
        $eventDeals = $participant->getUserButNotNull()->event->eventType->getEventDeals($participant);
        $allActiveDeals = [];

        foreach($eventDeals as $eventDeal) {
            $deal = $participant->findDeal($eventDeal->slug);
            if ($deal === null) {
                $deal = $this->createNewDeal($eventDeal, $participant);
            }

            $allActiveDeals[] = $deal;
        }

        return $allActiveDeals;
    }

    private function createNewDeal(
        EventDeal $eventDeal,
        Participant $participant,
    ): Deal {
        $deal = new Deal();
        $deal->slug = $eventDeal->slug;
        $deal->isDone = false;
        $deal->doneAt = null;
        $deal->urlAddress = $eventDeal->address;
        $deal->data = '';
        $deal->participant = $participant;

        $this->persist($deal);

        return $deal;
    }
}
