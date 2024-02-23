<?php

declare(strict_types=1);

namespace kissj\Deal;

use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
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

    /**
     * @param array<mixed> $jsonFromBody
     */
    public function trySaveNewDealFromGoogleForm(
        array $jsonFromBody,
        Event $authorizedEvent
    ): ?Deal {
        $tieCode = $jsonFromBody['TIE code'] ?? null;
        $dealSlug = $jsonFromBody['slug'] ?? null;
        if (!is_string($tieCode) || !is_string($dealSlug)) {
            return null;
        }

        $deal = $this->findDeal($dealSlug, $tieCode);
        if ($deal === null) {
            return null;
        }

        if ($authorizedEvent->id !== $deal->participant->getUserButNotNull()->event->id) {
            return null;
        }

        $isDone = true;
        if (isset($jsonFromBody['enoughPoints'])) {
            $isDone = (bool)$jsonFromBody['enoughPoints'];
        }

        $deal->data = json_encode($jsonFromBody, JSON_THROW_ON_ERROR);
        $deal->doneAt = DateTimeUtils::getDateTime();
        $deal->isDone = $isDone;
        $this->persist($deal);

        return $deal;
    }

    private function findDeal(string $slug, string $tieCode): ?Deal
    {
        $qb = $this->createFluent();
        $qb->where('slug = %s', $slug);
        $qb->join('participant')->as('participant')->on('participant.id = deal.participant_id');
        $qb->where('participant.tie_code = %s', $tieCode);

        /** @var ?Row $row */
        $row = $qb->fetch();
        if ($row === null) {
            return null;
        }

        /** @var Deal $deal */
        $deal = $this->createEntity($row);

        return $deal;
    }
}
