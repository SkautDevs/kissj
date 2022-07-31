<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Cej;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeCej extends EventType
{
    public const CONTINGENT_CZECHIA = 'detail.contingent.czechia';
    public const CONTINGENT_SLOVAKIA = 'detail.contingent.slovakia';
    public const CONTINGENT_POLAND = 'detail.contingent.poland';
    public const CONTINGENT_HUNGARY = 'detail.contingent.hungary';
    public const CONTINGENT_EUROPEAN = 'detail.contingent.european';
    public const CONTINGENT_ROMANIA = 'detail.contingent.romania';
    public const CONTINGENT_ISRAEL = 'detail.contingent.israel';
    public const CONTINGENT_TEAM = 'detail.contingent.team';

    public function getPrice(Participant $participant): int
    {
        if ($participant->contingent === self::CONTINGENT_TEAM) {
            return 1450;
        }

        $price = match (true) {
            $participant instanceof PatrolLeader => (count($participant->patrolParticipants) * 5800) + 2900,
            $participant instanceof Ist => 2900,
            default => throw new \Exception('Unknown participant class'),
        };

        if ($participant instanceof PatrolLeader && $participant->contingent === self::CONTINGENT_CZECHIA) {
            $price += count($participant->patrolParticipants) * 400;
        }

        return $price;
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            return match ($participant->contingent) {
                self::CONTINGENT_CZECHIA => 50,
                self::CONTINGENT_SLOVAKIA => 20,
                self::CONTINGENT_POLAND => 40,
                self::CONTINGENT_HUNGARY => 20,
                self::CONTINGENT_EUROPEAN => 30,
                self::CONTINGENT_ROMANIA => 20,
                self::CONTINGENT_ISRAEL => 10,
                default => 0,
            };
        }

        return parent::getMaximumClosedParticipants($participant);
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_cej.yaml',
        ];
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
        $ca->uploadFile = true;

        return $ca;
    }

    /**
     * @inheritDoc
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
            'detail.foodGlutenNoTraces',
            'detail.foodLactoseNoTraces',
            'detail.foodNoMilk',
            'detail.foodLactoseAndGlutenFree',
            'detail.foodLactoseFreeVegetarian',
            'detail.foodKosher',
            'detaiů.foodHalal',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countryCzechRepublic',
            'detail.countrySlovakia',
            'detail.countryPoland',
            'detail.countryHungary',
            'detail.countryRomania',
            'detail.countryOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContingents(): array
    {
        return [
            self::CONTINGENT_CZECHIA,
            self::CONTINGENT_SLOVAKIA,
            self::CONTINGENT_POLAND,
            self::CONTINGENT_HUNGARY,
            self::CONTINGENT_ROMANIA,
            self::CONTINGENT_ISRAEL,
            self::CONTINGENT_EUROPEAN,
            self::CONTINGENT_TEAM,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLanguages(): array
    {
        return [
            'en' => '🇬🇧 English',
        ];
    }
}
