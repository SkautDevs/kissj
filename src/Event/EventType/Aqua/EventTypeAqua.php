<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Aqua;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;

class EventTypeAqua extends EventType
{
    /**
     * Participants pays 150€ till 15/3/20, 160€ from 16/3/20, staff 50€
     * discount 40€ for self-eating participant (not for ISTs)
     * TODO update
     */
    public function getPrice(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            $todayPrice = $this->getFullPriceForToday();
            $patrolPriceSum = 0;
            /** @var list<PatrolLeader|PatrolParticipant> $fullPatrol */
            $fullPatrol = array_merge([$participant], $participant->patrolParticipants);
            foreach ($fullPatrol as $patrolParticipant) {
                $patrolPriceSum += $todayPrice;
                if ($patrolParticipant->foodPreferences === Participant::FOOD_OTHER) {
                    $patrolPriceSum -= 40;
                }
            }

            return $patrolPriceSum;
        }

        if ($participant instanceof Ist) {
            return 60;
        }

        return parent::getPrice($participant);
    }

    private function getFullPriceForToday(): int
    {
        if (DateTimeUtils::getDateTime() <= DateTimeUtils::getDateTime('2022-12-20')) {
            return 150;
        }

        return 160;
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_aqua.yaml',
            'sk' => __DIR__ . '/sk_aqua.yaml',
            'cs' => __DIR__ . '/cs_aqua.yaml',
        ];
    }

    #[\Override]
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->country->allowed = true;
        $ca->country->options = ContentArbiterItem::selfMappedOptions($this->getParticipantCountries());
        $ca->idNumber->allowed = true;
        $ca->languages->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->swimming->allowed = true;
        $ca->tshirt->allowed = true;
        $ca->driver->allowed = true;
        $ca->skills->allowed = true;
        $ca->preferredPosition->allowed = true;
        $ca->preferredPosition->options = ContentArbiterItem::selfMappedOptions($this->getPositionOptions());
        $ca->unit->allowed = true;

        return $ca;
    }

    #[\Override]
    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $ca = parent::getContentArbiterPatrolLeader();
        $ca->country->allowed = true;
        $ca->country->options = ContentArbiterItem::selfMappedOptions($this->getParticipantCountries());
        $ca->idNumber->allowed = true;
        $ca->languages->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->phone->allowed = true;
        $ca->tshirt->allowed = true;
        $ca->email->allowed = true;
        $ca->swimming->allowed = true;
        $ca->unit->allowed = true;

        return $ca;
    }

    #[\Override]
    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $ca = parent::getContentArbiterPatrolParticipant();
        $ca->country->allowed = true;
        $ca->country->options = ContentArbiterItem::selfMappedOptions($this->getParticipantCountries());
        $ca->idNumber->allowed = true;
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->phone->allowed = true;
        $ca->tshirt->allowed = true;
        $ca->email->allowed = true;
        $ca->swimming->allowed = true;
        $ca->unit->allowed = true;
        $ca->parentalConsent->allowed = true;

        return $ca;
    }

    /**
     * @inheritDoc
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countrySlovakia',
            'detail.countryCzechRepublic',
            'detail.countryOther',
        ];
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
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPositionOptions(): array
    {
        return [
            'detail.position.press',
            'detail.position.centralServices',
            'detail.position.technical',
            'detail.position.program.sailing',
            'detail.position.program.canoeing',
            'detail.position.program.communityProject',
            'detail.position.program.rafting',
            'detail.position.program.hiking',
            'detail.position.program.workshops',
            'detail.position.program.climbing',
            'detail.position.program.channel',
            'detail.position.program.tour',
            'detail.position.program.leisure',
            'detail.position.program.teahouse',
            'detail.position.program.subcamp',
        ];
    }
}
