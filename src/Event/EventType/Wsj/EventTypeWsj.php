<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Wsj;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\EventType\EventType;

class EventTypeWsj extends EventType
{
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
    public function getPreferredPositionsOptions(): array
    {
        return array_merge(
            parent::getPreferredPositionsOptions(),
            [
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
                'detail.position.krcma',
                'detail.position.video',
            ]
        );
    }
}
