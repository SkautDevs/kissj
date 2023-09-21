<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Obrok;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Troop\TroopLeader;

class EventTypeObrok extends EventType
{
    public function getPrice(Participant $participant): int
    {
        $now = DateTimeUtils::getDateTime();
        return match (true) {
            $participant instanceof Ist => 300,
            $participant instanceof TroopLeader => (count($participant->troopParticipants) + 1) * $this->getTroopPrice($now),
            default => throw new \Exception('Unknown participant class'),
        };
    }

    private function getTroopPrice(\DateTimeImmutable $now): int
    {
        return match (true) {
            $now < DateTimeUtils::getDateTime('2023-03-31 23:59:59') => 600,
            $now < DateTimeUtils::getDateTime('2023-05-01 23:59:59') => 700,
            $now < DateTimeUtils::getDateTime('2023-07-31 23:59:59') => 800,
            default => 900,
        };
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
