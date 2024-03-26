<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Korbo;

use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;

class EventTypeKorbo extends EventType
{
    private const int SCARF_PRICE = 100;

    protected function getPrice(Participant $participant): int
    {
        $price = 600;
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += self::SCARF_PRICE;
        }

        return $price;
    }

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();

        $ca->phone = true;
        $ca->email = true;
        $ca->country = true;
        $ca->unit = true;
        $ca->scarf = true;
        $ca->uploadFile = true;
        $ca->skills = true;

        return $ca;
    }

    public function getTranslationFilePaths(): array
    {
        return ["cs" => __DIR__ . "/cs.yaml"];
    }

    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesKorbo.css';
    }

    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }
}
