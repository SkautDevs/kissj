<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Obrok;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Troop\TroopLeader;

class EventTypeObrok extends EventType
{
    public function getPrice(Participant $participant): int
    {
        return match (true) {
            $participant instanceof Ist => 1000,
            $participant instanceof TroopLeader => (count($participant->troopParticipants) + 1) * 1600,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->tshirt = true;

        return $ca;
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_obrok.yaml',
        ];
    }

    public function getEventSpecificStyles(): string
    {
        $styles = file_get_contents(__DIR__ . '/stylesObrok.css');
        if ($styles === false) {
            return '';
        }

        return $styles;
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

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }
}
