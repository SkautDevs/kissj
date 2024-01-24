<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use DateInterval;
use DateTimeImmutable;
use kissj\Application\StringUtils;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\Event;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;

abstract class EventType
{
    public function getPrice(Participant $participant): int
    {
        return $participant->getUserButNotNull()->event->defaultPrice;
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        $event = $participant->getUserButNotNull()->event;

        return match (get_class($participant)) {
            PatrolLeader::class => $event->maximalClosedPatrolsCount ?? 0,
            TroopLeader::class => $event->maximalClosedTroopLeadersCount ?? 0,
            TroopParticipant::class => $event->maximalClosedTroopParticipantsCount ?? 0,
            Ist::class => $event->maximalClosedIstsCount ?? 0,
            Guest::class => $event->maximalClosedGuestsCount ?? 0,
            default => throw new \RuntimeException('Unexpected participant class: ' . get_class($participant)),
        };
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        return new ContentArbiterPatrolLeader();
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        return new ContentArbiterPatrolParticipant();
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        return new ContentArbiterIst();
    }

    public function getContentArbiterGuest(): ContentArbiterGuest
    {
        return new ContentArbiterGuest();
    }

    public function getContentArbiterTroopLeader(): ContentArbiterTroopLeader
    {
        return new ContentArbiterTroopLeader();
    }

    public function getContentArbiterTroopParticipant(): ContentArbiterTroopParticipant
    {
        return new ContentArbiterTroopParticipant();
    }

    public function isSellingIstTicketsAllowed(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationFilePaths(): array
    {
        return [];
    }

    public function getStylesheetNameWithoutLeadingSlash(): ?string
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodVegetarian',
            // 'detail.foodVegan',
            'detail.foodLactoseFree',
            'detail.foodGlutenFree',
            'detail.foodOther',
        ];
    }

    /**
     * @return array<string>
     */
    public function getPositionOptions(): array
    {
        return [
            'detail.position.photo',
            'detail.position.kitchen',
            'detail.position.security',
            'detail.position.hygiene',
            'detail.position.programme',
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
            'detail.countryOther',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return [
            'cs' => '🇨🇿 Česky',
            'sk' => '🇸🇰 Slovensky',
            'en' => '🇬🇧 English',
        ];
    }

    /**
     * @return array<string>
     */
    public function getContingents(): array
    {
        return [];
    }

    public function isUnlockExpiredButtonAllowed(): bool
    {
        return false;
    }

    public function showParticipantInfoInMail(): bool
    {
        return true;
    }

    public function calculatePaymentDueDate(DateTimeImmutable $dateFrom): DateTimeImmutable
    {
        // TODO edit to take 14 days or day before event start date, whatever comes sooner
        return $dateFrom->add(DateInterval::createFromDateString('14 days'));
    }

    public function isMultiplePaymentsAllowed(): bool
    {
        return false;
    }

    public function isLoginEmailAllowed(): bool
    {
        return true;
    }

    public function isLoginSkautisAllowed(): bool
    {
        return false;
    }

    public function isReceiptAllowed(): bool
    {
        return false;
    }

    public function getReceiptNumber(string $eventPrefix, Participant $participant, string $paymentId): string
    {
        return sprintf(
            '%s-%s-%s',
            $eventPrefix,
            StringUtils::padWithZeroes((string)$participant->id, 4),
            StringUtils::padWithZeroes($paymentId, 4),
        );
    }

    public function getMinimalPpCount(Event $event, Participant $participant): int
    {
        return $event->minimalPatrolParticipantsCount ?? 0;
    }

    public function getMaximalPpCount(Event $param, Participant $participant): int
    {
        return $param->maximalPatrolParticipantsCount ?? 0;
    }

    public function showIban(): bool
    {
        return false;
    }

    public function getSwift(): ?string
    {
        return null;
    }

    public function getConstantSymbol(): ?string
    {
        return null;
    }
}
