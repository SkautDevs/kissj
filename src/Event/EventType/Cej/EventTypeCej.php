<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Cej;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\Event;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeCej extends EventType
{
    final public const CONTINGENT_CZECHIA = 'detail.contingent.czechia';
    final public const CONTINGENT_SLOVAKIA = 'detail.contingent.slovakia';
    final public const CONTINGENT_POLAND = 'detail.contingent.poland';
    final public const CONTINGENT_HUNGARY = 'detail.contingent.hungary';
    final public const CONTINGENT_EUROPEAN = 'detail.contingent.european';
    final public const CONTINGENT_ROMANIA = 'detail.contingent.romania';
    final public const CONTINGENT_ISRAEL = 'detail.contingent.israel';
    final public const CONTINGENT_BRITAIN = 'detail.contingent.britain';
    final public const CONTINGENT_SWEDEN = 'detail.contingent.sweden';
    final public const CONTINGENT_TEAM = 'detail.contingent.team';

    public function getPrice(Participant $participant): int
    {
        if ($participant->contingent === self::CONTINGENT_TEAM) {
            return 150;
        }

        $price = match (true) {
            $participant instanceof PatrolLeader => (count($participant->patrolParticipants) * 250) + 250,
            $participant instanceof Ist => 150,
            default => throw new \Exception('Unknown participant class'),
        };

        return $price;
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            return match ($participant->contingent) {
                self::CONTINGENT_CZECHIA => 25,
                self::CONTINGENT_SLOVAKIA => 24,
                self::CONTINGENT_POLAND => 6,
                self::CONTINGENT_HUNGARY => 10,
                self::CONTINGENT_EUROPEAN => 6,
                self::CONTINGENT_BRITAIN => 6,
                self::CONTINGENT_SWEDEN => 10,
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
            'detail.foodLactoseAndGlutenFree',
            'detail.foodGlutenFreeVegetarian',
            'detail.foodOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countrySlovakia',
            'detail.countryCzechRepublic',
            'detail.countryPoland',
            'detail.countryHungary',
            'detail.countryOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContingents(): array
    {
        return [
            self::CONTINGENT_SLOVAKIA,
            self::CONTINGENT_CZECHIA,
            self::CONTINGENT_HUNGARY,
            self::CONTINGENT_POLAND,
            self::CONTINGENT_EUROPEAN,
            self::CONTINGENT_BRITAIN,
            self::CONTINGENT_SWEDEN,
            self::CONTINGENT_TEAM,
        ];
    }

    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesCej24.css';
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

    public function getMinimalPpCount(Event $event, Participant $participant): int
    {
        if (in_array(
            $participant->contingent,
            [
                self::CONTINGENT_CZECHIA,
                self::CONTINGENT_SLOVAKIA,
                self::CONTINGENT_POLAND,
                self::CONTINGENT_HUNGARY,
            ],
            true
        )) {
            return 9;
        }

        return $event->minimalPatrolParticipantsCount ?? 0;
    }

    public function getMaximalPpCount(Event $param, Participant $participant): int
    {
        if (in_array(
            $participant->contingent,
            [
                self::CONTINGENT_CZECHIA,
                self::CONTINGENT_SLOVAKIA,
                self::CONTINGENT_POLAND,
                self::CONTINGENT_HUNGARY,
            ],
            true
        )) {
            return 9;
        }

        return $param->maximalPatrolParticipantsCount ?? 0;
    }

    public function showIban(): bool
    {
        return true;
    }

    public function getSwift(): ?string
    {
        return 'TATRSKBX'; // TODO move into DB like IBAN
    }

    public function getConstantSymbol(): ?string
    {
        return '0558'; // TODO move into DB like IBAN
    }
}
