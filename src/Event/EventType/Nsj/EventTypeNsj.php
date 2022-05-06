<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Nsj;

use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;

class EventTypeNsj extends EventType
{
    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = new ContentArbiterIst();
        $ca->arrivalDate = true;
        $ca->departureDate = true;
        $ca->preferredPosition = true;
        $ca->food = true;
        $ca->tshirt = true;
        $ca->skills = true;

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
    public function getPositionOptions(): array
    {
        return [
            // TODO Veverka will create final list
            'detail.position.photo',
            'detail.position.kitchen',
            'detail.position.security',
            'detail.position.hygiene',
            'detail.position.programme',
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
