<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Nsj;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\EventType\EventType;

class EventTypeNsj extends EventType
{
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = new ContentArbiterIst();
        $ca->arrivalDate->allowed = true;
        $ca->departureDate->allowed = true;
        $ca->preferredPosition->allowed = true;
        $ca->preferredPosition->options = ContentArbiterItem::selfMappedOptions($this->getPositionOptions());
        $ca->food->allowed = true;
        $ca->food->options = ContentArbiterItem::selfMappedOptions($this->getFoodOptions());
        $ca->tshirt->allowed = true;
        $ca->skills->allowed = true;

        return $ca;
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'cs' => __DIR__ . '/cs_nsj.yaml',
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
            'detail.position.programme',
            'detail.position.photo',
            'detail.position.kitchen',
            'detail.position.security',
            'detail.position.hygiene',
            'detail.position.stage',
            'detail.position.builds',
            'detail.position.registration',
            'detail.position.subcamps',
            'detail.position.people',
        ];
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
}
