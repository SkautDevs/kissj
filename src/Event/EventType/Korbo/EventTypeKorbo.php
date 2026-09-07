<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Korbo;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterOrganizingTeam;
use kissj\Event\ContentArbiter\AgeGroup;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\Event;
use kissj\Event\EventType\EventType;
use kissj\Participant\Participant;

class EventTypeKorbo extends EventType
{
    private const int SCARF_PRICE = 150;
    private const int LOW_PRICE_BUFFER = 150;
    private const string LOW_PRICE_END = '2026-08-31 23:59:59';


    #[\Override]
    public function getPrice(Participant $participant): int
    {
        $price = parent::getPrice($participant);

        $closeDate = $participant->registrationCloseDate;
        if ($closeDate !== null && $closeDate > DateTimeUtils::getDateTime(self::LOW_PRICE_END)) {
            $price += self::LOW_PRICE_BUFFER;
        }
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += self::SCARF_PRICE;
        }

        return $price;
    }

    /**
     * @return list<array{price: int, scarf: bool}>
     */
    // tiers derive from the event's CURRENT defaultPrice, but payment prices are frozen at creation -
    // a later defaultPrice change dumps older payments into the report's 'other' row
    #[\Override]
    public function getFinanceTiers(Event $event): array
    {
        $basePrice = $event->defaultPrice;

        return [
            ['price' => $basePrice, 'scarf' => false],
            ['price' => $basePrice + self::LOW_PRICE_BUFFER, 'scarf' => false],
            ['price' => $basePrice + self::SCARF_PRICE, 'scarf' => true],
            ['price' => $basePrice + self::LOW_PRICE_BUFFER + self::SCARF_PRICE, 'scarf' => true],
        ];
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

        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->country->allowed = true;
        $ca->country->options = ContentArbiterItem::selfMappedOptions($this->getParticipantCountries());
        $ca->unit->allowed = true;
        $ca->emergencyContact->allowed = true;
        $ca->emergencyContact->ageGroup = AgeGroup::Under18;
        $ca->parentalConsent->allowed = true;
        $ca->parentalConsent->required = true;
        $ca->scarf->allowed = true;
        $ca->gender->allowed = false;
        $ca->scarf->order = 410;

        return $ca;
    }

    #[\Override]
    public function getContentArbiterOrganizingTeam(): ContentArbiterOrganizingTeam
    {
        $ca = parent::getContentArbiterOrganizingTeam();

        $ca->phone->allowed = true;
        $ca->email->allowed = true;
        $ca->country->allowed = true;
        $ca->country->options = ContentArbiterItem::selfMappedOptions($this->getParticipantCountries());
        $ca->unit->allowed = true;
        $ca->emergencyContact->allowed = true;
        $ca->emergencyContact->ageGroup = AgeGroup::Under18;
        $ca->parentalConsent->allowed = true;
        $ca->parentalConsent->required = true;
        $ca->scarf->allowed = true;
        $ca->gender->allowed = false;
        $ca->scarf->order = 410;

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
    public function isBadgeGenerationAllowed(): bool
    {
        return false;
    }

    #[\Override]
    public function getBadgeStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/badgeKorbo.css';
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
    public function isLoginSkautisAllowed(): bool
    {
        return true;
    }

    #[\Override]
    public function showParticipantInfoInMail(): bool
    {
        return false;
    }

    #[\Override]
    public function isLoginEmailAllowed(): bool
    {
        return false;
    }


    #[\Override]
    public function showFoodStats(): bool
    {
        return false;
    }

    #[\Override]
    public function isOwnerTicketTransferAllowed(): bool
    {
        return true;
    }
}
