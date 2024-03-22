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
use kissj\Participant\Troop\TroopParticipant;

class EventTypeObrok extends EventType
{
    public const string SLUG_SFH = 'sfh';
    public const string SLUG_PROGRAMME = 'programme';

    public const string CONTINGENT_VOLUNTEER = 'detail.contingent.volunteer';
    public const string CONTINGENT_ORG = 'detail.contingent.org';

    #[\Override]
    protected function getPrice(Participant $participant): int
    {
        return match (true) {
            $participant instanceof Ist => 500,
            $participant instanceof TroopLeader => (count($participant->troopParticipants) + 1) * 1600,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    #[\Override]
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->gender = false;
        $ca->contingent = true;
        $ca->medicaments = true;
        $ca->printedHandbook = true;

        return $ca;
    }

    #[\Override]
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

    #[\Override]
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
    #[\Override]
    public function getContingents(): array
    {
        return [
            self::CONTINGENT_VOLUNTEER,
            self::CONTINGENT_ORG,
        ];
    }

    #[\Override]
    public function showIstImport(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_obrok.yaml',
        ];
    }

    #[\Override]
    public function getStylesheetNameWithoutLeadingSlash(): ?string
    {
        return 'eventSpecificCss/stylesObrok.css';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    #[\Override]
    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }

    #[\Override]
    public function isLoginEmailAllowed(): bool
    {
        return false;
    }

    #[\Override]
    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }

    #[\Override]
    public function isReceiptAllowed(): bool
    {
        return true;
    }

    #[\Override]
    public function getEventDeals(Participant $participant): array
    {
        $eventDeals = [
            new EventDeal(
                self::SLUG_SFH,
                sprintf(
                    'https://docs.google.com/forms/d/e/1FAIpQLSezG__WHx4N8Jdq3Lj626bbYHgPMovwcFT_97DS4WdCPQBQgA/viewform?usp=pp_url&entry.68270341=%s',
                    $participant->tieCode,
                ),
            ),
        ];

        if ($participant instanceof TroopLeader || $participant instanceof TroopParticipant) {
            $eventDeals[] = new EventDeal(
                self::SLUG_PROGRAMME,
                sprintf(
                    'https://docs.google.com/forms/d/e/1FAIpQLScHeou_NyNKNmUOjpGaBLMlhMy0gB6xoal6xcMTyc84EpJcNw/viewform?usp=pp_url&entry.2082059253=%s&entry.797747193=%s',
                    $participant->tieCode,
                    $participant->getFullName(),
                ),
            );
        }

        return $eventDeals;
    }
}
