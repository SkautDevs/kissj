<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Obrok;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Deal\EventDeal;

class EventTypeObrok extends EventType
{
    public const SLUG_SFH = 'sfh';
    public const SLUG_PROGRAMME = 'programme';

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
        $ca->medicaments = true;
        $ca->printedHandbook = true;
        return $ca;
    }

    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        $ca = parent::getContentArbiterTroopLeader();
        $ca->gender = false;
        $ca->phone = true;
        $ca->email = true;
        $ca->unit = true;
        $ca->medicaments = true;
        $ca->printedHandbook = true;

        return $ca;
    }

    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        $ca = parent::getContentArbiterTroopParticipant();
        $ca->gender = false;
        $ca->phone = true;
        $ca->email = true;
        $ca->unit = true;
        $ca->medicaments = true;
        $ca->printedHandbook = true;

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

    public function getStylesheetNameWithoutLeadingSlash(): ?string
    {
        return 'eventSpecificCss/stylesObrok.css';
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

    public function isLoginEmailAllowed(): bool
    {
        return false;
    }

    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }

    public function isReceiptAllowed(): bool
    {
        return true;
    }

    public function getEventDeals(Participant $participant): array
    {
        return [
            new EventDeal(
                self::SLUG_SFH,
                sprintf(
                    'https://docs.google.com/forms/d/e/1FAIpQLSed6rvnmKgtooyr1Dk6CCLhftCYiYap-nXPcLTVYKIlHQwbUg/viewform?entry.221441438=%s',
                    $participant->tieCode,
                ),
            ),
            new EventDeal(
                self::SLUG_PROGRAMME,
                'https://example.com/programme',
            ),
        ];
    }
}
