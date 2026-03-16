<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Jj;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeJj extends EventType
{
    private const int SCARF_PRICE = 200;

    public function getPrice(Participant $participant): int
    {
        $price = match (true) {
            $participant instanceof Ist => 300,
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 650,
            default => parent::getPrice($participant),
        };

        if ($participant->scarf === Participant::SCARF_YES) {
            $price += self::SCARF_PRICE;
        }

        if ($participant instanceof PatrolLeader) {
            foreach ($participant->patrolParticipants as $patrolParticipant) {
                if ($patrolParticipant->scarf === Participant::SCARF_YES) {
                    $price += self::SCARF_PRICE;
                }
            }
        }

        return $price;
    }

    #[\Override]
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $caIst = parent::getContentArbiterIst();
        $caIst->phone->allowed = true;
        $caIst->unit->allowed = true;
        $caIst->food->allowed = true;
        $caIst->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $caIst->scarf->allowed = true;
        $caIst->swimming->allowed = true;
        $caIst->skills->allowed = true;

        return $caIst;
    }

    #[\Override]
    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $caPl = parent::getContentArbiterPatrolLeader();
        $caPl->phone->allowed = true;
        $caPl->unit->allowed = true;
        $caPl->food->allowed = true;
        $caPl->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $caPl->scarf->allowed = true;
        $caPl->swimming->allowed = true;

        return $caPl;
    }

    #[\Override]
    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $caPp = parent::getContentArbiterPatrolParticipant();
        $caPp->phone->allowed = true;
        $caPp->unit->allowed = true;
        $caPp->food->allowed = true;
        $caPp->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $caPp->scarf->allowed = true;
        $caPp->swimming->allowed = true;

        return $caPp;
    }

    #[\Override]
    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        $caGuest = parent::getContentArbiterGuest();

        $caGuest->address->allowed = true;
        $caGuest->gender->allowed = true;
        $caGuest->birthDate->allowed = true;
        $caGuest->health->allowed = true;
        $caGuest->psychicalHealth->allowed = true;

        $caGuest->phone->allowed = true;
        $caGuest->unit->allowed = true;
        $caGuest->food->allowed = true;
        $caGuest->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $caGuest->scarf->allowed = true;
        $caGuest->swimming->allowed = true;
        $caGuest->skills->allowed = true;

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
    protected function isReceiptAllowed(): bool
    {
        return true;
    }
}
