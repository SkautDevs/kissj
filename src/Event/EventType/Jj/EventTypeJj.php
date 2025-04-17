<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Jj;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeJj extends EventType
{
    protected function getPrice(Participant $participant): int
    {
        return match (true) {
            $participant instanceof Ist => 300,
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 650,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->phone = true;
        $caIst->unit = true;
        $caIst->food = true;
        $caIst->scarf = true;
        $caIst->swimming = true;
        $caIst->skills = true;

        return $caIst;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $caPl = parent::getContentArbiterPatrolLeader();
        $caPl->phone = true;
        $caPl->unit = true;
        $caPl->food = true;
        $caPl->scarf = true;
        $caPl->swimming = true;

        return $caPl;
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $caPp = parent::getContentArbiterPatrolParticipant();
        $caPp->phone = true;
        $caPp->unit = true;
        $caPp->food = true;
        $caPp->scarf = true;
        $caPp->swimming = true;

        return $caPp;
    }

    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        $caGuest = parent::getContentArbiterGuest();

        $caGuest->address = true;
        $caGuest->gender = true;
        $caGuest->birthDate = true;
        $caGuest->health = true;
        $caGuest->psychicalHealth = true;

        $caGuest->phone = true;
        $caGuest->unit = true;
        $caGuest->food = true;
        $caGuest->scarf = true;
        $caGuest->swimming = true;
        $caGuest->skills = true;

        return $caGuest;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_jj.yaml',
        ];
    }

    #[\Override]
    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesJj25.css';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
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
    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }

    #[\Override]
    public function isReceiptAllowed(): bool
    {
        return true;
    }
}
