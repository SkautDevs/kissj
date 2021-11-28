<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Cej;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeCej extends EventType
{
    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_cej.yaml',
        ];
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            return match ($participant->country) {
                'Slovak' => 15,
                'Czech' => 10,
                default => 20,
            };
        }

        return parent::getMaximumClosedParticipants($participant);
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->contingent = true;
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->tshirt = true;
        $ca->skills = true;
        $ca->unit = true;

        return $ca;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $ca = parent::getContentArbiterPatrolLeader();
        $ca->contingent = true;
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;

        return $ca;
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $ca = parent::getContentArbiterPatrolParticipant();
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;

        return $ca;
    }
    
    /**
     * @return array<string>
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodHalfMeat',
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
    public function getParticipantCountries(): array
    {
        return [
            'detail.countryCzechRepublic',
            'detail.countrySlovakia',
            'detail.countryPoland',
            'detail.countryHungary',
            'detail.countryOther'
        ];
    }
    
    /**
     * @return string[]
     */
    public function getContingents(): array
    {
        return [
            'detail.contingent.czechia',
            'detail.contingent.slovakia',
            'detail.contingent.poland',
            'detail.contingent.hungary',
            'detail.contingent.european',
            'detail.contingent.team',
        ];
    }
    
    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'en' => '🇬🇧 English',
        ];
    }
}
