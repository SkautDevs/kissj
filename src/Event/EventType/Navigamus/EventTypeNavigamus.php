<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Navigamus;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeNavigamus extends EventType
{
    public const string CONTINGENT_VOLUNTEER = 'detail.contingent.volunteer';
    public const string CONTINGENT_ORG = 'detail.contingent.org';

    protected function getPrice(Participant $participant): int
    {
        $now = DateTimeUtils::getDateTime();
        $patrolPrice = match (true) {
            $now < DateTimeUtils::getDateTime('2025-02-28 23:59:59') => 1300,
            $now < DateTimeUtils::getDateTime('2025-03-31 23:59:59') => 1500,
            $now < DateTimeUtils::getDateTime('2025-04-30 23:59:59') => 1800,
            default => 2800,
        };

        return match (true) {
            $participant instanceof Ist => 900,
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * $patrolPrice,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->contingent = true;
        $caIst->food = true;
        $caIst->preferredPosition = true;
        $caIst->tshirt = true;
        $caIst->arrivalDate = true;
        $caIst->departureDate = true;

        return $caIst;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $caPl = parent::getContentArbiterPatrolLeader();
        $caPl->food = true;

        return $caPl;
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $caPp = parent::getContentArbiterPatrolParticipant();
        $caPp->food = true;

        return $caPp;
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

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_navigamus.yaml',
        ];
    }

    #[\Override]
    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesNavigamus25.css';
    }

    /**
     * @return array<string>
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodVegetarian',
            'detail.foodVegan',
            'detail.foodLactoseFree',
            'detail.foodGlutenFree',
            'detail.foodOther',
        ];
    }

    /**
     * @return array<string>
     */
    public function getPositionOptions(): array
    {
        return [
            'detail.position.registration',
            'detail.position.kitchen',
            'detail.position.hygiene',
            'detail.position.site',
            'detail.position.security',
            'detail.position.programme',
        ];
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

    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }
}
