<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Korbo;

use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;

class EventTypeKorbo extends EventType
{
    private const int SCARF_PRICE = 100;

    #[\Override]
    protected function getPrice(Participant $participant): int
    {
        $price = 600;
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += self::SCARF_PRICE;
        }

        return $price;
    }

    #[\Override]
    public function isUnlockExpiredButtonAllowed(): bool
    {
        return true;
    }

    #[\Override]
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

    #[\Override]
    public function getTranslationFilePaths(): array
    {
        return ["cs" => __DIR__ . "/cs.yaml"];
    }

    #[\Override]
    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesKorbo.css';
    }

    #[\Override]
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    #[\Override]
    public function enforceActiveSkautisMembership(): bool
    {
        return true;
    }

    #[\Override]
    public function showFoodStats(): bool
    {
        return false;
    }
}
