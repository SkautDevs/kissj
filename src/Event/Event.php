<?php

declare(strict_types=1);

namespace kissj\Event;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Application\ImageUtils;
use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Miquik\EventTypeMiquik;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Nsj\EventTypeNsj;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Event\EventType\Wsj\EventTypeWsj;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

/**
 * @property int               $id
 * @property string            $slug
 * @property string            $readableName
 * @property string            $webUrl
 * @property string            $dataProtectionUrl
 * @property string            $contactEmail
 * @property EventType         $eventType m:useMethods(getEventType|)
 * @property string            $logoUrl
 * @property bool              $testingSite
 *
 * @property string            $accountNumber with bank code after slash
 * @property string            $iban
 * @property string            $swift
 * @property int               $prefixVariableSymbol // TODO add null
 * @property string            $constantSymbol
 * @property bool              $automaticPaymentPairing
 * @property string            $bankSlug
 * @property string|null       $bankApiKey
 * @property int               $defaultPrice
 * @property string            $currency
 *
 * @property bool              $allowPatrols
 * @property int|null          $maximalClosedPatrolsCount
 * @property int|null          $minimalPatrolParticipantsCount
 * @property int|null          $maximalPatrolParticipantsCount
 *
 * @property bool              $allowIsts
 * @property int|null          $maximalClosedIstsCount
 *
 * @property bool              $allowGuests
 * @property int|null          $maximalClosedGuestsCount
 *
 * @property bool              $allowTroops
 * @property int|null          $maximalClosedTroopLeadersCount
 * @property int|null          $maximalClosedTroopParticipantsCount
 * @property int|null          $minimalTroopParticipantsCount
 * @property int|null          $maximalTroopParticipantsCount
 *
 * @property int|null          $maximalClosedParticipantsCount
 *
 * @property DateTimeInterface $startRegistration m:passThru(dateFromString|dateToString) // in UTC
 * @property DateTimeInterface $startDay m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface $endDay m:passThru(dateFromString|dateToString)
 *
 * @property string            $emailFrom
 * @property string            $emailFromName
 * @property string|null       $emailBccFrom
 *
 * @property string            $apiSecret
 */
class Event extends EntityDatetime
{
    public function getEventType(): EventType
    {
        $eventTypeClass = match ($this->row->event_type) {
            'default' => EventTypeDefault::class,
            'aqua' => EventTypeAqua::class,
            'cej' => EventTypeCej::class,
            'korbo' => EventTypeKorbo::class,
            'miquik' => EventTypeMiquik::class,
            'navigamus' => EventTypeNavigamus::class,
            'nsj' => EventTypeNsj::class,
            'wsj' => EventTypeWsj::class,
            'obrok' => EventTypeObrok::class,
            default => throw new \RuntimeException('unknown event type: ' . $this->row->event_type),
        };

        return new $eventTypeClass();
    }

    public function canRegistrationBeLocked(): bool
    {
        return $this->startRegistration <= DateTimeUtils::getDateTime();
    }

    /**
     * @return ParticipantRole[]
     */
    public function getAvailableRoles(): array
    {
        $roles = [];
        if ($this->allowPatrols) {
            $roles[] = ParticipantRole::PatrolLeader;
            $roles[] = ParticipantRole::PatrolParticipant;
        }
        if ($this->allowTroops) {
            $roles[] = ParticipantRole::TroopLeader;
            $roles[] = ParticipantRole::TroopParticipant;
        }
        if ($this->allowIsts) {
            $roles[] = ParticipantRole::Ist;
        }
        if ($this->allowGuests) {
            $roles[] = ParticipantRole::Guest;
        }

        return $roles;
    }

    public function getLogoInBase64(): string
    {
        return ImageUtils::getLocalImageInBase64(self::getFullLogoUrl($this->logoUrl));
    }

    public static function getFullLogoUrl(string $logoUrl): string
    {
        return "/assets" . $logoUrl;
    }

    public function getMinimalPpCount(Participant $participant): int
    {
        return $this->eventType->getMinimalPpCount($this, $participant);
    }

    public function getMaximalPpCount(Participant $participant): int
    {
        return $this->eventType->getMaximalPpCount($this, $participant);
    }
}
