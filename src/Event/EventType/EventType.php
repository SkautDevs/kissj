<?php declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

abstract class EventType
{
    public function getPrice(Participant $participant): int
    {
        return $participant->getUserButNotNull()->event->defaultPrice;
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        $event = $participant->getUserButNotNull()->event;

        return match (get_class($participant)) {
            PatrolLeader::class => $event->maximalClosedPatrolsCount ?? 0,
            Ist::class => $event->maximalClosedIstsCount ?? 0,
            Guest::class => $event->maximalClosedGuestsCount ?? 0,
            default => throw new \RuntimeException('Unexpected participent class: ' . get_class($participant)),
        };
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        return new ContentArbiterPatrolLeader();
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        return new ContentArbiterPatrolParticipant();
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        return new ContentArbiterIst();
    }

    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        return new ContentArbiterGuest();
    }

    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        return new ContentArbiterTroopLeader();
    }

    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        return new ContentArbiterTroopParticipant();
    }

    public function isSellingIstTicketsAllowed(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodVegetarian',
            'detail.foodLactoseFree',
            'detail.foodGlutenFree',
            'detail.foodOther',
        ];
    }

    /**
     * @return array<string>
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countryCzechRepublic',
            'detail.countrySlovakia',
            'detail.countryOther',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
            'sk' => '🇸🇰 Slovensky',
            'en' => '🇬🇧 English',
        ];
    }

    /**
     * @return string[]
     */
    public function getContingents(): array
    {
        return [];
    }
}
