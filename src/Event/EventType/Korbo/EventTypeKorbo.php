<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Korbo;

use DateTimeImmutable;
use kissj\Event\ContentArbiterIst;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;

class EventTypeKorbo extends EventType
{
    private const SCARF_PRICE = 100;

    public function getPrice(Participant $participant): int
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

    public function getEventSpecificStyles(): string
    {
        $styles = file_get_contents(__DIR__ . '/stylesKorbo.css');
        if ($styles === false) {
            return '';
        }

        return $styles;
    }

    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
        ];
    }

    public function isSellingIstTicketsAllowed(): bool
    {
        return true;
    }

    public function calculatePaymentDueDate(DateTimeImmutable $dateFrom): DateTimeImmutable
    {
        // TODO remove for 2024, hotfix only
        return new DateTimeImmutable('2023-09-18');
    }
}
