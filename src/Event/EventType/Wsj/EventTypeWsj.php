<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Wsj;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
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
                default => throw new \Exception('Unknown participant class for payment count 0̈́'),
            },
            1 => match (true) {
                $participant instanceof TroopLeader,
                $participant instanceof TroopParticipant,
                    => 15_000,
                $participant instanceof Ist,
                    => 12_000,
                default => throw new \Exception('Unknown participant class for payment count 1'),
            },
            2 => match (true) {
                $participant instanceof TroopLeader,
                $participant instanceof TroopParticipant,
                    => 17_000,
                default => throw new \Exception('Unknown participant class for payment count 2'),
            },
            default => throw new \Exception('Problematic payment count: ' . $paymentsCount),
        };
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_wsj.yaml',
            'cs' => __DIR__ . '/cs_wsj.yaml',
        ];
    }

    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        $ca = parent::getContentArbiterTroopLeader();

        $ca->patrolName = false;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;
        $ca->skills = true;

        return $ca;
    }

    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        $ca = parent::getContentArbiterTroopParticipant();

        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;
        $ca->uploadFile = true;

        return $ca;
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();

        $ca->unit = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->food = true;
        $ca->skills = true;
        $ca->preferredPosition = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->swimming = true;

        return $ca;
    }

    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    /**
     * @return array<string>
     */
    public function getPositionOptions(): array
    {
        return [
            'detail-ist.position.photo',
            'detail-ist.position.photoMeets',
            'detail-ist.position.supportJOMeet',
            'detail-ist.position.supportISTMeet',
            'detail-ist.position.logistics',
            'detail-ist.position.fundraising',
            'detail-ist.position.pr',
            'detail-ist.position.expo',
            'detail-ist.position.merchandise',
            'detail-ist.position.graphics',
            'detail-ist.position.zza',
            'detail-ist.position.englishLector',
            'detail-ist.position.wsj',
            'detail-ist.position.video',
            'detail-ist.position.program',
        ];
    }
}
