<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Wsj;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;

class EventTypeWsj extends EventType
{
    public function getPrice(Participant $participant): int
    {
        $paymentsCount = count($participant->getNoncanceledPayments());

        return match ($paymentsCount) {
            0 => match (true) {
                $participant instanceof TroopLeader,
                $participant instanceof TroopParticipant,
                $participant instanceof Ist,
                => 12_000,
                default => parent::getPrice($participant),
            },
            1 => match (true) {
                $participant instanceof TroopLeader,
                $participant instanceof TroopParticipant,
                => 15_000,
                $participant instanceof Ist,
                => 12_000,
                default => parent::getPrice($participant),
            },
            2 => match (true) {
                $participant instanceof TroopLeader,
                $participant instanceof TroopParticipant,
                => 17_000,
                default => parent::getPrice($participant),
            },
            default => throw new \Exception('Problematic payment count: ' . $paymentsCount),
        };
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_wsj.yaml',
            'cs' => __DIR__ . '/cs_wsj.yaml',
        ];
    }

    #[\Override]
    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        $ca = parent::getContentArbiterTroopLeader();

        $ca->patrolName->allowed = false;
        $ca->idNumber->allowed = true;
        $ca->languages->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->swimming->allowed = true;
        $ca->unit->allowed = true;
        $ca->skills->allowed = true;

        return $ca;
    }

    #[\Override]
    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        $ca = parent::getContentArbiterTroopParticipant();

        $ca->idNumber->allowed = true;
        $ca->languages->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->swimming->allowed = true;
        $ca->unit->allowed = true;
        $ca->parentalConsent->allowed = true;

        return $ca;
    }

    #[\Override]
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();

        $ca->unit->allowed = true;
        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->skills->allowed = true;
        $ca->preferredPosition->allowed = true;
        $ca->preferredPosition->options = ContentArbiterItem::selfMappedOptions($this->getPositionOptions());
        $ca->idNumber->allowed = true;
        $ca->languages->allowed = true;
        $ca->swimming->allowed = true;

        return $ca;
    }

    /**
     * @inheritDoc
     */
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPositionOptions(): array
    {
        return [
            'detail.position.photo',
            'detail.position.photoMeets',
            'detail.position.supportJOMeet',
            'detail.position.supportISTMeet',
            'detail.position.logistics',
            'detail.position.fundraising',
            'detail.position.pr',
            'detail.position.expo',
            'detail.position.merchandise',
            'detail.position.graphics',
            'detail.position.zza',
            'detail.position.englishLector',
            'detail.position.wsj',
            'detail.position.video',
            'detail.position.program',
        ];
    }

    public function isMultiplePaymentsAllowed(): bool
    {
        return true;
    }
}
